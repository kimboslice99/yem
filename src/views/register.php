<?php 

require __DIR__ . '/../csrf.php';
require __DIR__ . '/mysqli.php';
require __DIR__ . '/admin/util.php';
require __DIR__ . '/abuseipdb.php';


if(isset($_SESSION['name'])) {
    header('Location: /');
}

$error = false;
$state[0] = $state[1] = $state[2] = $state[3] = $state[4] = $state[5] = $phone = $lastname = $firstname = $email = $address = '';
$errormsg = array();
if(isset($_POST['register']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
  if(AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) { $error = true && array_push($errormsg, 'code: 69') ; }
	if(strlen($_POST['firstname']) < 2) { $error = true && array_push($errormsg, 'First name must be at least two characters') && $state[0] = 'is-invalid' ; }
	if(strlen($_POST['lastname']) < 2) { $error = true && array_push($errormsg, 'Last name must be at least two characters') && $state[1] = 'is-invalid' ; }
	if(strlen($_POST['email']) < 4) { $error = true && array_push($errormsg, 'Email must be at least five characters') && $state[2] = 'is-invalid' ; }  // Check input, set error=true if under specific string length
	if(strlen(preg_replace('/[^0-9]/', '', $_POST['phone'])) < 10) { $error = true && array_push($errormsg, 'Phone must be at least 10 digits') && $state[3] = 'is-invalid' ; } // Also, error messages 
	if(strlen($_POST['address']) < 15) { $error = true && array_push($errormsg, 'Address must be at least 15 Characters') && $state[4] = 'is-invalid' ; }
	if(strlen($_POST['password']) < 8) { $error = true && array_push($errormsg, 'Password must be at least 8 characters') && $state[5] = 'is-invalid' ; }
  $lastname = filter_input(INPUT_POST, 'lastname');
  $firstname = filter_input(INPUT_POST, 'firstname');
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $phone = filter_input(INPUT_POST, 'phone');
  $address = filter_input(INPUT_POST, 'address');
  $password = password_hash(filter_input(INPUT_POST, 'password'), PASSWORD_DEFAULT);
  $createdTime = time();
  $statement = $mysqli->prepare("SELECT * FROM users WHERE email=?");
  $statement->bind_param('s', $email);
  $statement->execute();
  $result = $statement->get_result();
  if($statement->affected_rows > 0) {$error=true && array_push($errormsg, 'E-Mail in use!<br>') && $state[2]='is-invalid';}// email exists in database/user already signed up
  if($error === false) {
		$statement = $mysqli->prepare("INSERT INTO users (firstname, lastname, email, phone, address, password, created) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$statement->bind_param('sssssss', $firstname, $lastname, $email, $phone, $address, $password, $createdTime);
		$statement->execute();
		$result = $statement->get_result(); // for whatever reason we have to do statement->get_result in order to get affected_rows
		if($statement->affected_rows > 0) {
			sendEmail(array($email), "Welcome!", "Thanks for registering!", false, null, null);
			$_SESSION['name'] = $lastname . ' ' . $firstname;
			$_SESSION['email'] = $email;
			$_SESSION['phone'] = $phone;
			$_SESSION['address'] = $address;
			$_SESSION['created-time'] = $createdTime;
			header('Location: /');
		} else {
			$error = true && array_push($errormsg, 'Error occurred, please report');
		}
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <title><?= $config['title'] ?></title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="<?= $config['description'] ?>">
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
<section>
    <div>
        <div class="div-center">
        <?php if($error): ?>
            <div>
                <div>
                    <div class="alertPart">
                    <div class="alert alert-danger errreg" role="alert"><i class="tf-ion-close-circled"></i><span>Registration Failed! </span>The following errors have occurred! <ul><?php foreach($errormsg as $msg){echo '<li>'.$msg.'<li>';} ?></ul></div>
                    </div>
                </div>		
            </div>
        <?php endif ?>
        <div class="">
            <div class="block text-center">
            <a href="/">
					<img class="logo-h" src="/images/logo.jpg" alt="Logo">
            </a>
            <form class="text-left clearfix requires-validation" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" novalidate>
                <?= CSRF::csrfInputField() ?>
                <div class="form-group p-2">
                    <input type="text" name="firstname" minlength="2" class="form-control <?= $state[0] ?>" value="<?= $firstname ?>" placeholder="Firstname" required>
				  <div class="valid-feedback font-10">
					 Firstname looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 Firstname is required!
				  </div>
                </div>
                <div class="form-group p-2">
                    <input type="text" name="lastname" minlength="2" class="form-control <?= $state[1] ?>" value="<?= $lastname ?>" placeholder="Lastname" required>
				  <div class="valid-feedback font-10">
					 Lastname looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 Lastname is required!
				  </div>
                </div>
                <div class="form-group p-2">
                    <input type="email" name="email" minlength="4" class="form-control <?= $state[2] ?>" value="<?= $email ?>" placeholder="Email" required>
				  <div class="valid-feedback font-10">
					 E-Mail looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 E-Mail is required!
				  </div>
                </div>
                <div class="form-group p-2">
                    <input type="tel" name="phone" minlength="10" class="form-control <?= $state[3] ?>" value="<?= $phone ?>" placeholder="Phone" required>
				  <div class="valid-feedback font-10">
					 Phone looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 Phone is required!
				  </div>
                </div>
                <div class="form-group p-2">
                    <input type="text" name="address" minlength="15" class="form-control <?= $state[4] ?>" value="<?= $address ?>" placeholder="Address" required>
				  <div class="valid-feedback font-10">
					 Address looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 Address is required!
				  </div>
                </div>
                <div class="form-group p-2">
                    <input type="text" name="password" minlength="8" id="pswd" class="form-control <?= $state[5] ?>" placeholder="Password" required>
				  <div class="valid-feedback font-10">
					 Password looks good!
				  </div>
				  <div class="invalid-feedback font-10">
					 Password is required!
				  </div>
                </div>
                <div class="text-center p-2">
                    <button name="register" type="submit" class="btn btn-main text-center" >Register</button>
                </div>
            </form>
            </div>
        </div>
        </div>
    </div>
</section>
 <!-- 
    Essential Scripts
    =====================================-->
	<!-- Validator -->
    <script src="/js/validator.js"></script>
    <!-- Main jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- Popper 2.x -->
    <!--<script src="views/plugins/popper/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3"></script>-->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- Bootstrap 5.2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <!-- Bootstrap Touchpin -->
    <script src="/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Count Down Js -->
    <script src="/plugins/syo-timer/build/jquery.syotimer.min.js"></script>
    <!-- slick Carousel
    <script src="/plugins/slick/slick.min.js"></script>
    <script src="/plugins/slick/slick-animation.min.js"></script> Unsure why this was loaded for the registration page?-->
    <!-- Main Js File -->
    <script src="/js/script.js"></script>
    
    

</body>
</html>
