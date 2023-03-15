<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../mysqli.php';
require __DIR__ . '/../../csrf.php';
require __DIR__ . '/../abuseipdb.php';
require __DIR__ . '/util.php';

$error = false;

if(isset($_POST['policy-submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	$policy = filter_input(INPUT_POST, 'policy');
	$id = 1;
    $statement = $mysqli->prepare("UPDATE policy SET policy=? WHERE id=?");
	$statement->bind_param('si', $policy, $id);
    $statement->execute();
}

if(isset($_POST['about-submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	$about = filter_input(INPUT_POST, 'about');
	$id = 1;
    $statement = $mysqli->prepare("UPDATE about SET about=? WHERE id=?");
	$statement->bind_param('si'. $about, $id);
    $statement->execute();
}

if(isset($_POST['contact-submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
    if(isset($_POST['address'])) {
        config('address', filter_input(INPUT_POST, 'address'));
	}

    if(isset($_POST['email'])) {
        config('contact_email', filter_input(INPUT_POST, 'email'));
	}

    if(isset($_POST['phone'])) {
        config('contact_phone', filter_input(INPUT_POST, 'phone'));
	}

    if(isset($_POST['facebook'])) {
        config('contact_facebook', filter_input(INPUT_POST, 'facebook'));
	}

    if(isset($_POST['twitter'])) {
        config('contact_twitter', filter_input(INPUT_POST, 'twitter'));
	}

    if(isset($_POST['instagram'])) {
        config('contact_instagram', filter_input(INPUT_POST, 'instagram'));
	}
	
}
if(isset($_POST['app-submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	if(isset($_POST['appid'])) {
		config('title', filter_input(INPUT_POST, 'appid'));
	}
	if(isset($_POST['description'])) {
		config('description', filter_input(INPUT_POST, 'description'));
	}
	if(isset($_POST['abuseipdb'])) {
		config('abuseipdb', filter_input(INPUT_POST, 'abuseipdb'));
	}
	if(isset($_POST['tax_rates'])) {
		config('tax', filter_input(INPUT_POST, 'tax_rates'));
	}
	if(isset($_POST['currency_symbol'])) {
		config('currency_symbol', filter_input(INPUT_POST, 'currency_symbol'));
	}
	if(isset($_POST['clam_path'])) {
		config('clam_path', filter_input(INPUT_POST, 'clam_path'));
	}
	if(isset($_POST['clam_config_path'])) {
		config('clam_config_path', filter_input(INPUT_POST, 'clam_config_path'));
	}
	if(isset($_FILES['files']) && $_FILES["files"]["error"][0] == 0) {
		if(!empty($config['meta_image'])) { unlink(__DIR__ . '/../../' . $config['meta_image']); }
		$img = uploadImages();
		config('meta_image', $img[0]);
	}
	if(isset($_FILES['logo']) && $_FILES["logo"]["error"] == 0) {
		// Check if png
		$fileType = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
		if($fileType != 'jpg'){ $error = true; }
		// Double check that its really png
		$handle = fopen($_FILES["logo"]["tmp_name"], 'r');
		$bytes = strtoupper(bin2hex(fread($handle, 4)));
		fclose($handle);
		if(!in_array($bytes, array('FFD8FFE0'))) { $error = true; }
		if(clamdscan($_FILES["logo"]["tmp_name"])){ $error = true; }
		if(!$error){
			move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ .'/../../images/logo.jpg');
			//compressImage($_FILES["logo"]["tmp_name"], __DIR__ .'/../../images/logo.jpg', 75, 600, 1500, 'white');
		}
	}
	if(isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
		// Check if png
		$fileType = pathinfo($_FILES["favicon"]["name"], PATHINFO_EXTENSION);
		if($fileType != 'png'){ $error = true; }
		// Double check that its really png
		$handle = fopen($_FILES["favicon"]["tmp_name"], 'r');
		$bytes = strtoupper(bin2hex(fread($handle, 4)));
		fclose($handle);
		if(!in_array($bytes, array('89504E47'))) { $error = true; }
		if(clamdscan($_FILES["favicon"]["tmp_name"])){ $error = true; }
		if(!$error){
			//move_uploaded_file($_FILES['favicon']['tmp_name'], __DIR__ .'/../../images/favicon.png');
			compressImage($_FILES["favicon"]["tmp_name"], __DIR__ .'/../../images/favicon.png', 75, 32, 32, 'transparent');
		}
	}
}

if(isset($_POST['bt-submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	if(isset($_POST['bt_autoloader'])) {
		config('bt_autoloader', filter_input(INPUT_POST, 'bt_autoloader'));
	}
	if(isset($_POST['environment'])) {
		config('environment', filter_input(INPUT_POST, 'environment'));
	}
	if(isset($_POST['merchantid'])) {
		config('merchantid', filter_input(INPUT_POST, 'merchantid'));
	}
	if(isset($_POST['public_key'])) {
		config('public_key', filter_input(INPUT_POST, 'public_key'));
	}
	if(isset($_POST['private_key'])) {
		config('private_key', filter_input(INPUT_POST, 'private_key'));
	}
}


if(isset($_POST['smtp-submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	if(isset($_POST['mail_from_address'])) {
		config('mail_from_address', filter_input(INPUT_POST, 'mail_from_address', FILTER_SANITIZE_EMAIL));
	}
	if(isset($_POST['mail_from_name'])) {
		config('mail_from_name', filter_input(INPUT_POST, 'mail_from_name'));
	}
	if(isset($_POST['mail_to_address'])) {
		config('mail_to_address', filter_input(INPUT_POST, 'mail_to_address', FILTER_SANITIZE_EMAIL));
	}
	if(isset($_POST['smtp_host'])) {
		config('smtp_host', filter_input(INPUT_POST, 'smtp_host', FILTER_SANITIZE_EMAIL));
	}
	if(isset($_POST['smtp_port'])) {
		config('smtp_port', filter_input(INPUT_POST, 'smtp_port', FILTER_SANITIZE_EMAIL));
	}
	if(isset($_POST['smtp_username'])) {
		config('smtp_username', filter_input(INPUT_POST, 'smtp_username', FILTER_SANITIZE_EMAIL));
	}
	if(isset($_POST['smtp_password'])) {
		config('smtp_password', filter_input(INPUT_POST, 'smtp_password', FILTER_SANITIZE_EMAIL));
	}
}

$statement = $mysqli->prepare("SELECT * FROM policy");
$statement->execute();
$result = $statement->get_result();
if($statement->affected_rows > 0) {
	$privacyPolicy = $result->fetch_assoc();
} else {
	$privacyPolicy['policy'] = '';
}

$statement = $mysqli->prepare("SELECT * FROM about");
$statement->execute();
$result = $statement->get_result();
if($statement->affected_rows > 0) {
	$about = $result->fetch_assoc();
} else {
	$about['about'] = '';
}

// Reload config, show current values after update
$config = parse_ini_file(__DIR__ . '/../../config/config.ini');
$csrf = CSRF::csrfInputField();
?>
<div class="container">
    <div class="page-title">
        <h3>Settings</h3>
    </div>
    <div class="box box-primary">
        <div class="box-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="system-tab" data-bs-toggle="tab" href="#system" role="tab" aria-controls="system" aria-selected="false">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="attributions-tab" data-bs-toggle="tab" href="#attributions" role="tab" aria-controls="attributions" aria-selected="false">Privacy Policy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="app-tab" data-bs-toggle="tab" href="#app" role="tab" aria-controls="app" aria-selected="false">Application Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="bt-tab" data-bs-toggle="tab" href="#bt" role="tab" aria-controls="bt" aria-selected="false">Braintree details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="smtp-tab" data-bs-toggle="tab" href="#smtp" role="tab" aria-controls="smtp" aria-selected="false">SMTP Settings</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade active show" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="col-md-6">
                        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">
                            <?= $csrf ?>
                            <div class="mb-3">
                                <textarea class="form-control" required rows="20" name="about"><?= $about['about'] ?></textarea>
                            </div>
                            <div class="mb-3 text-end">
                                <button name="about-submit" class="btn btn-success" type="submit"><i class="fas fa-check"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                    <div class="col-md-6">
                        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">
                            <div class="mb-3">
                                <div class="input-group mb-3">
                                    <?= $csrf ?>
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-home"></i></span>
                                    <input type="text" name="address" class="form-control" value="<?= $config['address'] ?>">
                                    <input name="ad-id" value="<?= $config['address'] ?>" hidden>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">The below can be left empty and we'll hide their icons if so</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><b>@</b></span>
                                    <input type="email" name="email" class="form-control" value="<?= $config['contact_email'] ?>">
                                    <input name="em-id" value="<?= $config['contact_email'] ?>" hidden>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="phone" class="form-control" value="<?= $config['contact_phone'] ?>">
                                    <input name="ph-id" value="<?= $config['contact_phone'] ?>" hidden>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><b>f</b></span>
                                    <input type="text" name="facebook" class="form-control" value="<?= $config['contact_facebook'] ?>">
                                    <input name="fb-id" value="<?= $config['contact_facebook'] ?>" hidden>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><b>IG</b></span>
                                    <input type="text" name="instagram" class="form-control" value="<?= $config['contact_instagram'] ?>">
                                    <input name="ig-id" value="<?= $config['contact_instagram'] ?>" hidden>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-retweet"></i></span>
                                    <input type="text" name="twitter" class="form-control" value="<?= $config['contact_twitter'] ?>">
                                    <input name="tw-id" value="<?= $config['contact_twitter'] ?>" hidden>
                                </div>
                            </div>
                            <div class="mb-3 text-end">
                                <button class="btn btn-success" name="contact-submit" type="submit"><i class="fas fa-check"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="attributions" role="tabpanel" aria-labelledby="attributions-tab">
                    <h4 class="mb-0">Legal Notice</h4>
                    <p class="text-muted">Copyright &copy;<?= date('Y') ?> Yem-Yem. All rights reserved.</p>
                    <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">
                        <?= $csrf ?>
                        <textarea class="form-control" name="policy" required rows="20"><?= $privacyPolicy['policy'] ?></textarea>            
                        <div class="mb-3 text-end">
                            <button class="btn btn-success" name="policy-submit" type="submit"><i class="fas fa-check"></i> Update</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="app" role="tabpanel" aria-labelledby="app-tab">
                    <div class="col-md-8">
                        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="requires-validation" enctype="multipart/form-data" novalidate>
                            <?= $csrf ?>
                            <div class="mb-3">
								<label class="form-label font-10">&lt;title>&lt;/title> and &lt;meta property="og:title" content=""></label>
                                <div class="input-group mb-3">
                                    <input type="text" name="appid" class="form-control" placeholder="<title></title> and <meta property='og:title' content=''>" value="<?= $config['title'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">&lt;meta name="description" content=""> and &lt;meta property="og:description" content=""></label>
                                <div class="input-group mb-3">
                                    <input type="text" name="description" class="form-control" placeholder="<meta name='description' content=''> and <meta property='og:description' content=''>" value="<?= $config['description'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">AbuseIPDB Api Key (disabled for local addresses)</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="abuseipdb" class="form-control" placeholder="AbuseIPDB Api Key" value="<?= $config['abuseipdb'] ?>">
                                </div>
                                    <?= (empty(AbuseIPDB::GetKey())) ? '<p class="font-14 text-danger text-center">AbuseIPDB Disabled!</p>':'<p class="font-14 text-success text-center">AbuseIPDB Enabled!</p>'; ?>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">ClamAV - enter full clamdscan path here</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="clam_path" class="form-control" placeholder="/path/to" value="<?= $config['clam_path'] ?>">
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">ClamAV config path</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="clam_config_path" class="form-control" placeholder="/config/path" value="<?= $config['clam_config_path'] ?>">
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Tax rates, comma seperated for multiple</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="tax_rates" class="form-control" placeholder="3,5" value="<?= $config['tax'] ?>">
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Currency Symbol</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="currency_symbol" class="form-control" placeholder="$" value="<?= $config['currency_symbol'] ?>">
                                </div>
                            </div>
							<div class="mb-3">
								<label class="form-label font-10">Meta property og:image</label>
							  <div class="input-group">
								<input class="form-control h-25" name="files[]" type="file" id="formFile1">
<?php if(!empty($config['meta_image'])): ?><img width="55" height="55" src="/<?= $config['meta_image'] ?>"><?php endif ?>
							  </div>
							</div>
							<div class="mb-3">
								<label class="form-label font-10">Logo</label>
							  <div class="input-group">
								<input class="form-control h-25" accept=".jpg" name="logo" type="file" id="formFile1">
								<img height="25" src="/images/logo.jpg">
							  </div>
							</div>
							<div class="mb-3">
								<label class="form-label font-10">Favicon</label>
							  <div class="input-group">
								<input class="form-control h-25" name="favicon" accept=".png" type="file" id="formFile1">
								<img height="35" width="35" src="/images/favicon.png">
							  </div>
							</div>
                            <div class="mb-3 text-end">
                                <button name="app-submit" class="btn btn-success" type="submit"><i class="fas fa-check"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="bt" role="tabpanel" aria-labelledby="bt-tab">
                    <div class="col-md-8">
                        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="requires-validation" novalidate>
                            <?= $csrf ?>
                            <div class="mb-3">
								<label class="form-label font-10">Braintree autoloader path</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="bt_autoloader" class="form-control" placeholder="/path/to" value="<?= $config['bt_autoloader'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Environment</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="environment" class="form-control" placeholder="Sandbox" value="<?= $config['environment'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Merchant ID</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="merchantid" class="form-control" placeholder="abcd1234" value="<?= $config['merchantid'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Public key</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="public_key" class="form-control" placeholder="abcd1234" value="<?= $config['public_key'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Private key</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="private_key" class="form-control" placeholder="abcd1234" value="<?= $config['private_key'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 text-end">
                                <button name="bt-submit" class="btn btn-success" type="submit"><i class="fas fa-check"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="smtp" role="tabpanel" aria-labelledby="smtp-tab">
                    <div class="col-md-8">
                        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="requires-validation" novalidate>
                            <?= $csrf ?>
                            <div class="mb-3">
								<label class="form-label font-10">Mail From (Name)</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="mail_from_name" class="form-control" placeholder="Mail From (Name)" value="<?= $config['mail_from_name'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Mail From (Address)</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="mail_from_address" class="form-control" placeholder="no-reply@domain.tld" value="<?= $config['mail_from_address'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Mail To (Address) - for contact form</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="mail_to_address" class="form-control" placeholder="support@domain.tld" value="<?= $config['mail_to_address'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">Host</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="smtp_host" class="form-control" placeholder="127.0.0.1" value="<?= $config['smtp_host'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">SMTP Port</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="smtp_port" class="form-control" placeholder="25" value="<?= $config['smtp_port'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">SMTP Username (leave these blank for no AUTH)</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="smtp_username" class="form-control" placeholder="walter.white@yourdomain.tld" value="<?= $config['smtp_username'] ?>">
                                </div>
                            </div>
                            <div class="mb-3">
								<label class="form-label font-10">SMTP Password</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="smtp_password" class="form-control" placeholder="abcd1234" value="<?= $config['smtp_password'] ?>">
                                </div>
                            </div>
                            <div class="mb-3 text-end">
                                <button name="smtp-submit" class="btn btn-success" type="submit"><i class="fas fa-check"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
	<!-- Validator -->
    <script src="/js/validator.js"></script>


<?php
require __DIR__ . '/footer.php';
 ?>