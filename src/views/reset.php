<?php

require __DIR__ . '/../csrf.php';
require __DIR__ . '/db.php';
require __DIR__ . '/abuseipdb.php';

if(isset($_SESSION['name'])) {
    header('Location: /');
}

if(!isset($_GET['email'])) {
    header('Location: /login');
}

$errorcode = 0;
$success;
$error = false;
if(isset($_POST['submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
	if(AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) { $error = true ; }
	  if(!empty($_POST['code']) && (!$error)) {
		$code = filter_input(INPUT_POST, 'code');
		$statement = $pdo->prepare("SELECT * FROM users WHERE email=?");
		$statement->execute(array(filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL)));
		if($statement->rowCount() > 0) {
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			if($code == $result[0]['code'] && time() <= $result[0]['expiration']) {
				$success= true;
				$_SESSION['tmp_code'] = $code;
			} else {
				$success = false;
				$errorcode = 1;
			}
		}
		else {
			$success = false;
			$errorcode = 2;
		}
	  }
	  else {
		  $success = false;
		  $errorcode = 69;
	  }
}

if(isset($_POST['reset']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
	if(AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) { $error = true ; }
	$sessioncheck = filter_var($_SESSION['tmp_code']);
    $password = password_hash(filter_input(INPUT_POST, 'password'), PASSWORD_DEFAULT);
    $statement = $pdo->prepare("UPDATE users SET password=?, code=?, expiration=? WHERE email=? AND code=?");
    $statement->execute(array($password, 0, 0, filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL), $sessioncheck));
	if($statement->rowCount() > 0 && !$error) {
		unset($_SESSION['tmp_code']);
		header('Location: /login');
	} else {
		$success = false;
		$errorcode = 4;
	}
}
$csrf = CSRF::csrfInputField();
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <title><?= $config['title'] ?> | Reset Password</title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="<?= $config['title'] ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.png" />
  
  <link rel="stylesheet" href="/plugins/themefisher-font/style.css">
  <!-- bootstrap.min css -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  
  <!-- Animate css -->
  <link rel="stylesheet" href="/plugins/animate/animate.css">
  <!-- Slick Carousel -->
  <link rel="stylesheet" href="/plugins/slick/slick.css">
  <link rel="stylesheet" href="/plugins/slick/slick-theme.css">
  
  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="/css/style.css">

</head>

<body id="body">

<section class="forget-password-page account">
  <div class="div-center">
    <div class="row">
        <?php if(isset($success)): ?>
            <?php if($success): ?>
                <div>
                    <div class="block text-center">
                        <form class="text-left clearfix requires-validation" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" novalidate>
                            <?= $csrf ?>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
                            </div>
                            <div class="text-center">
                                <button name="reset" type="submit" class="btn btn-main text-center" >Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif(!$success): ?>
                <div class="alert alert-danger errreg" role="alert">
                    <i class="tf-ion-android-checkbox-outline"></i>Code Invalid/Expired<br><?= $errorcode ?>
                </div>
            <?php endif ?>
        <?php else: ?>
            <div>
                <div class="block text-center">
                <form class="text-left clearfix requires-validation" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" novalidate>
                    <p>Enter the code sent to your email.</p>
                    <?= $csrf ?>
                    <div class="form-group">
                    <input type="text" name="code" class="form-control" id="exampleInputEmail1" placeholder="Enter code" required>
				  <div class="valid-feedback font-10">
					 Code looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 Code is required!
				  </div>
                    </div>
                    <div class="text-center">
                    <button type="submit" name="submit" class="btn btn-main text-center">Submit</button>
                    </div>
                </form>
                </div>
            </div>
        <?php endif ?>
    </div>
  </div>
</section>

   <!-- 
    Essential Scripts
    =====================================-->
    
    <!-- Main jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- Popper 2.11.6 -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- Bootstrap 5.3.0 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <!-- Bootstrap Touchpin -->
    <script src="views/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Count Down Js -->
    <script src="/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel -->
    <script src="/plugins/slick/slick.min.js"></script>
    <script src="/plugins/slick/slick-animation.min.js"></script>

    <!-- Main Js File -->
    <script src="/js/script.js"></script>
    <script src="/js/validator.js"></script>
    


  </body>
  </html>