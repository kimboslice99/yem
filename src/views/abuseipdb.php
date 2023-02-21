<?php

class AbuseIPDB {
		static public function GetKeyDb() {
			require __DIR__ . '/db.php';
			$statement = $pdo->prepare("SELECT * FROM app_settings WHERE name=?");
			$statement->execute(array('abuseipdb'));
			if($statement->rowCount() > 0) {
			$abuseipdb = $statement->fetchAll();
			return $abuseipdb[0]['value'];
			} else {
				return '';
			}
		}
		static public function GetKey() {
			$config = parse_ini_file(__DIR__ . '/bin/config.ini');
			if(empty($config['abuseipdb'])){
				return '';
			} else {
				return $config['abuseipdb'];
			}
		}
		// Returns true if above confidence score, or if api errors, else return false & also return false when no key is present
		static public function Listed($ipaddress, $confidence) {
			// Check if key exists
			if(empty(self::GetKey())){return false;}
			// Make sure its not local!
			if(preg_match('/(^127\.)|(^192\.168\.)|(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^::1$)|(^[fF][cCdD])/i', $ipaddress)){return false;}
			//url encode the colons in ipv6 addresses
			$urlencodedip = urlencode($ipaddress);
			$ch = curl_init('https://api.abuseipdb.com/api/v2/check?ipAddress=' . $urlencodedip);
			// Set headers
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Key: '.self::GetKey(), 'accept: application/json'));
			// check endpoint is GET
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
			// Return the result
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			// Decode response, (execute), associative true
			$response = json_decode(curl_exec($ch), true);
			// Well, duh
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			// Check for API Errors
			if($code != 200){return true;}
			// Also to try to catch errors
			if(curl_errno($ch)){return true;}
			// duh
			if($response['data']['abuseConfidenceScore'] > $confidence){return true;}
			// finally
			return false;
		}
		
		// Returns false on error, otherwise true
		static public function Report($ipaddress, $comment, $categories) {
			$urlencodedip = urlencode($ipaddress); //url encode the colons in ipv6 addresses
			$urlencodedcomment = urlencode($comment);
			$ch = curl_init('https://api.abuseipdb.com/api/v2/report?ip=' . $urlencodedip . '&comment=' . $urlencodedcomment);
			// Set headers
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Key: '.self::GetKey(), 'accept: application/json'));
			// Report endpoiont is POST
			curl_setopt($ch,CURLOPT_POST, 1);
			// Payload
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('categories' => $categories));
			// Get response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			// Return the result
			$response = json_decode(curl_exec($ch), true);

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($code != 200){return false;}
			if(curl_errno($ch)){return false;}
			return true;
		}
		
}
?>