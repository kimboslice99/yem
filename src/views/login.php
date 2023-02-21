<?php 

ob_start();

require __DIR__ . '/mysqli.php';
require __DIR__ . '/admin/util.php';
require __DIR__ . '/../csrf.php';
require __DIR__ . '/abuseipdb.php';


if(isset($_SESSION['name'])) {
    header('Location: /');
}

$error = false;
$errormsg = '';
if(isset($_POST['login']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
  if(!AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)){
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password');
    $statement = $mysqli->prepare("SELECT * FROM users WHERE email=?");
	$statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();
    if($statement->affected_rows > 0) {
        $assoc = $result->fetch_assoc();
        if(password_verify($password, $assoc['password'])) {
            $_SESSION['name'] = (string)$assoc['lastname'] . ' ' . $assoc['firstname'];
			$_SESSION['firstname'] = (string)$assoc['firstname'];
			$_SESSION['lastname'] = (string)$assoc['lastname'];
            $_SESSION['email'] = (string)$assoc['email'];
            $_SESSION['phone'] = (string)$assoc['phone'];
            $_SESSION['address'] = (string)$assoc['address'];
            $_SESSION['created-time'] = (string)$assoc['created'];
            $_SESSION['id'] = (string)$assoc['id'];
            header('Location: /');
        } else {
        $error = true;
		$errormsg = 'Invalid username/password';
		}
    } else {
    $error = true;
	$errormsg = 'Invalid username/password';
	}
  } else {
  $error = true;
  $errormsg = 'Code 69';
  }
}
$csrf = CSRF::csrfInputField();
?>

<!DOCTYPE html>
<html lang="en">
</style>
  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <title><?= $config['title'] ?></title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="<?= $config['description'] ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  
  <!-- Sharing needs -->
  <meta property="og:title" content="<?= $config['title'] ?>">
  <meta property="og:description" content="<?= $config['description'] ?>">
  <meta property="og:image" content="<?= $config['meta_image'] ?>">
  <meta property="og:url" content="<?= preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST']) ?>">
  
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

    <section class="signin-page account">
    <div class="back">
        <div class="div-center">
            
        <?php if($error):
file_put_contents(__DIR__ . '/bin/failed_user.txt', date('M/d/Y H:m:s') . ' Login Failed! ' . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND|LOCK_EX);?>
            <div class="row mt-30">
                <div class="col-xs-12">
                    <div class="alertPart">
                    <div class="alert alert-danger alert-common" role="alert"><i class="tf-ion-close-circled"></i><span>Login Failed!</span><ul><li><?= $errormsg ?></li></ul></div>
                    </div>
                </div>		
            </div>
        <?php endif ?>

        <div>
            <div class="block text-center">
            <a href="/">
				<img class="logo-h" src="/images/logo.jpg" alt="Logo">
            </a>
            <h2 class="text-center">Welcome Back</h2>
            <form class="text-left clearfix requires-validation" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" novalidate>
                <?= $csrf ?>
                <div class="form-group p-3">
                    <input type="email" name="email" class="form-control"  placeholder="Email" required>
                </div>
                <div class="form-group p-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="text-center">
                    <button name="login" type="submit" class="btn btn-main text-center" >Login</button>
                </div>
            </form>
            <p class="mt-20">Don't have an account ?<a href="/register"> Create New Account</a></p>
            <p class="mt-20"><a href="/forgot-password">Forgot Password?</a></p>
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
