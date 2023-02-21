<?php

require __DIR__ . '/db.php';
require __DIR__ . '/../csrf.php';
require __DIR__ . '/admin/util.php';
require __DIR__ . '/abuseipdb.php';

if(isset($_SESSION['name'])) {
    header('Location: /');
}

$success;
$errcode = $error = false;
if(isset($_POST['submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
  if(AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) { $error = true ; }
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $code = rand(10000, 99999);
  $expirationTime = time() * 30 * 60;
  $statement = $pdo->prepare("SELECT * FROM users WHERE email=?");
  $statement->execute(array($email));
    if(!$error) {
	  if($statement->rowCount() > 0) {
		$statement = $pdo->prepare("UPDATE users SET code=?, expiration=? WHERE email=?");
		$statement->execute(array($code, $expirationTime, $email));
		if($statement->rowCount() > 0) {
			sendEmail(array($email), "Password Reset", "Reset code: ". $code . "\n\nIf you didn't request for a password reset, ignore this message.", false, null);
			header('Location: /reset?email='. $email);
		} else {
			$success = false;
			$errcode = 3;
		}
	  } else {
	     $success = false;
		 $errcode = 1;
	  }
   } else {
	   $success = false;
	   $errcode = 69;
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <title><?= $config['title'] ?> | Forgot Password</title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="<?= $config['description'] ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.png" />
  
  <!-- Themefisher Icon font -->
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

<body>

<section class="forget-password-page account">
  <div class="div-center">
    <div class="row">
      <?php if(isset($success) && !$success): ?>
        <div class="alert alert-danger errreg" role="alert">
            <!--<button type="button" class="btn" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
            <i class="tf-ion-android-checkbox-outline"></i> Error code: <?= var_dump($errcode) ?><br> Account doesn't exist
        </div>
      <?php endif ?>
      <div>
        <div class="block text-center">
          <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
            <p>Please enter the email address for your account. A reset code will be sent to you.</p>
            <?= CSRF::csrfInputField() ?>
            <div class="form-group p-2">
              <input type="email" name="email" class="form-control" id="exampleInputEmail1" placeholder="Account email address">
            </div>
            <div class="text-center">
              <button type="submit" name="submit" class="btn btn-main text-center">Request password reset</button>
            </div>
          </form>
          <p class="mt-20"><a href="/login">Back to log in</a></p>
        </div>
      </div>
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
    <script src="/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Instagram Feed Js -->
    <script src="/plugins/instafeed/instafeed.min.js"></script>
    <!-- Count Down Js -->
    <script src="/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel -->
    <script src="/plugins/slick/slick.min.js"></script>
    <script src="/plugins/slick/slick-animation.min.js"></script>

    <!-- Main Js File -->
    <script src="/js/script.js"></script>
    <!-- <script type="module" src="js/index.js"></script> -->
    


  </body>
  </html>