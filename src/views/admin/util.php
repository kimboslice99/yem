<?php
// Import PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Require PHPMailer
require_once __DIR__.'/../bin/PHPMailer/Exception.php';
require_once __DIR__.'/../bin/PHPMailer/PHPMailer.php';
require_once __DIR__.'/../bin/PHPMailer/SMTP.php';

function sendEmail($emails, $title, $message, $html, $attachment, $image) {
	$config = parse_ini_file(__DIR__ . '/../../config/config.ini');
	(empty($config['smtp_username']) || empty($config['smtp_password']))?$auth=false:$auth=true;
		foreach($emails as $email) {
			//Create an instance; passing `true` enables exceptions
			$mail = new PHPMailer(true);
			try {
				
				//Server settings
				// $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
				$mail->isSMTP();
				$mail->Host       = $config['smtp_host'];
				$mail->SMTPAuth   = $auth;
				$mail->Username   = $config['smtp_username'];
				$mail->Password   = $config['smtp_password'];
				$mail->Port       = $config['smtp_port'];

				$mail->setFrom($config['mail_from_address'], $config['mail_from_name']);
				$mail->addAddress($email);
				/* Remember to swap addAttachment function variables $encoding and $type in PHPMailer if upgrading */
				($attachment != null)?$mail->addAttachment($attachment['name'], $attachment['tmp_name'], mime_content_type($attachment['tmp_name'])):''; // Add attachments
				($image != null)?$mail->addEmbeddedImage($image['path'], $image['name']):'';// Add image
				
				$mail->isHTML($html);
				$mail->Subject = $title;
				$mail->Body    = $message;
				$mail->XMailer = ' '; // Try to hide what language this site is built on

				$mail->send();
				return true;
			} catch (Exception $e) {
				 trigger_error("Message could not be sent. Error: " . $mail->ErrorInfo);
				return false;
			}
		}
}

function exportDB($compression, $db) {
	$filename = __DIR__.'/'.time();
	//create/open files
    $tables = array();
    $result = $db->query("SHOW TABLES");
    while($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $return = '';

    foreach($tables as $table){
        $result = $db->query("SELECT * FROM $table");
        $numColumns = $result->field_count;
		
	$return.= 'DROP TABLE IF EXISTS `'.$table.'`;';
	
        $result2 = $db->query("SHOW CREATE TABLE $table");
        $row2 = $result2->fetch_row();

        $return .= "\n\n".$row2[1].";\n\n";

        for($i = 0; $i < $numColumns; $i++) {
            while($row = $result->fetch_row()) {
                $return .= "INSERT INTO $table VALUES(";
                for($j=0; $j < $numColumns; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = $row[$j];
                    if (isset($row[$j])) {
                        $return .= '"'.$row[$j].'"' ;
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($numColumns-1)) {
                        $return.= ',';
                    }
                }
                $return .= ");\n";
            }
        }

        $return .= "\n-- ------------------------------------------------ \n\n";
    }

	if ($compression) {
	$zp = gzopen($filename = $filename.'.sql.gz', "a9");
	gzwrite($zp, $return);
	gzclose($zp);
	} else {
	$handle = fopen($filename = $filename.'.sql','a+');
	fwrite($handle, $return);
	fclose($handle);
	}

    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . basename($filename));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '. filesize($filename));
    header('Content-Type: application/sql');
    ob_clean();
    flush();
	readfile($filename);
	unlink($filename);
    die();
}

function importDB($tmp, $db) {
	$sql = file_get_contents($tmp);
	$db->multi_query($sql);
}
// debugging this function? some things to check
// - writeable upload_tmp_dir
// On IIS, ensure IIS_IUSRS has write access
// on apache, ensure www-data has write access (or whatever your webserver user is)
// - file_uploads=On in php.ini
// - upload_max_filesize set high enough
// - var_dump() in steps through code to see whats happening
function uploadImages() {

    // File upload configuration 
    $targetDir = "uploads/"; 
    $allowTypes = array('jpg','png','jpeg','gif', 'webp');
    $paths = array();
     

    $fileNames = array_filter($_FILES['files']['name']);
	
    if(!empty($fileNames)){ 
        foreach($_FILES['files']['name'] as $key=>$val){
            // File upload path
            $file = explode(".", $_FILES["files"]["name"][$key]);

            $fileName = md5(microtime(true)) . '.' . end($file);
            $targetFilePath = $targetDir . $fileName; 

            // Check whether file type is valid 
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            if(in_array($fileType, $allowTypes) && verifyMagicByte($_FILES["files"]["tmp_name"][$key])) {
				if(!clamdscan($_FILES['files']['tmp_name'][$key])){
					$imageTemp = $_FILES["files"]["tmp_name"][$key];
					$imageUploadPath = $targetDir . $fileName;
					$paths[] = compressImage($imageTemp, $imageUploadPath, 50, 500, 500, 'white');
				}
            }
        }

    }
    return $paths;
}


function verifyMagicByte($file) {
    // PNG, GIF, JFIF JPEG, EXIF JPEF, WEBP respectively
    $allowed = array('89504E47', '47494638', 'FFD8FFE0', 'FFD8FFE1', '52494646');
    $handle = fopen($file, 'r');
    $bytes = strtoupper(bin2hex(fread($handle, 4)));
    fclose($handle);
    return in_array($bytes, $allowed);
}


function compressImage($source, $destination, $quality, $height, $width, $bgcolor) { 
	// Remove Exif
	$img = new Imagick(realpath($source));
	$profiles = $img->getImageProfiles("icc", true);
    $img->stripImage();
	// Add color profile back, if exists
    if(!empty($profiles)) {
       $img->profileImage("icc", $profiles['icc']);
    }
	//$img->setFormat('jpeg'); // Convert to jpeg? hmm
	// Set quality
	$img->setImageCompressionQuality($quality);
	
	// Credits go to Finglish on StackOverflow for this!
    $img->trimImage(20000);

    $img->resizeImage($width, $height,Imagick::FILTER_LANCZOS,1, TRUE);
	if($bgcolor == 'transparent'){
		$img->setImageBackgroundColor(new \ImagickPixel('transparent'));
	}else{
		$img->setImageBackgroundColor($bgcolor);
	}

    $w = $img->getImageWidth();
    $h = $img->getImageHeight();

    $off_top=0;
    $off_left=0;

    if($w > $h){
        $off_top = intval((($height-$h)/2) * -1); // Added intval() to supress "Deprecated: Implicit conversion from float -xx.x to int loses precision" Warning
    }else{
        $off_left = intval((($width-$w)/2) * -1);
    }

    $img->extentImage($width,$height, $off_left, $off_top);

	$img->writeImage($destination);
	$img->clear();
	$img->destroy();

    // Return compressed image 
    return $destination; 
}
// this needs work
function cartcheck($item, $qty) {
	require __DIR__ . '/../mysqli.php';
    $statement = $mysqli->prepare("SELECT * FROM products WHERE id=?");
	$statement->bind_param('i', $item);
	$statement->execute();
	$result = $statement->get_result();
	if($statement->affected_rows > 0) {
		$stock = $result->fetch_assoc();
	} else {
		return -1; // Product Error
	}
	if($stock['qty'] - $qty >= 0) {
		return $stock['qty'] - $qty;
	} else {
		return -1;
	}
}
// Simple tax function
function tax($num, $rate) {
	$tax = 0;
	$tax += bcdiv(bcmul($num, $rate, 2), 100, 2);
	return $tax;
}

// Homegrown ini config function, retains comments, unlike some solutions
function config($key, $value){
	$path = __DIR__.'/../../config/config.ini';
	$f = file($path);
	$h = fopen($path, 'w');
	foreach($f as $line){
		if(preg_match('/^'.$key.'=.+/i', $line)){
			$line = preg_replace('/^'.$key.'=.+/i', $key.'="'.$value.'"', $line);
		}
		fwrite($h, $line);
	}
	fclose($h);
}
// Function requires clamd service running and config details entered
function clamdscan($filepath){
	$config = parse_ini_file(__DIR__ . '/../../config/config.ini');
	// if path empty return as you would if clean
	if(empty($config['clam_path'])){return false;}
	exec('"'.$config['clam_path'].'" -c "'.$config['clam_config_path'].'" --fdpass --stream "'.$filepath.'"', $null, $var);
	if($var === 0) {
		return false;
	}else{
		trigger_error('ClamAV marked ' . $filepath . ' as a virus, removing');
		return true;
	}
}