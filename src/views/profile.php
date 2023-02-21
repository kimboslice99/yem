<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/../csrf.php';
require __DIR__ . '/mysqli.php';

if(!isset($_SESSION['name'])) {
  header('Location: /login');
}
$error = false;
$errormsg = array();
$state[0] = $state[1] = $state[2] = $state[3] = '';
if(isset($_POST['update']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
	if(strlen($_POST['firstname']) < 2) { $error = true && array_push($errormsg, 'First name must be at least two characters<br>') && $state[0] = 'is-invalid' ; }
	if(strlen($_POST['lastname']) < 2) { $error = true && array_push($errormsg, 'Last name must be at least two characters<br>') && $state[1] = 'is-invalid' ; }
	if(strlen(preg_replace('/[^0-9]/', '', $_POST['phone'])) < 10) { $error = true && array_push($errormsg, 'Phone must be at least 10 digits<br>') && $state[2] = 'is-invalid' ; } // Also, error messages 
	if(strlen($_POST['address']) < 15) { $error = true && array_push($errormsg, 'Address must be at least 15 Characters<br>') && $state[3] = 'is-invalid' ; }
	if($error === false) {
	  if(isset($_POST['firstname'])) {
		$firstname = filter_input(INPUT_POST, 'firstname');
		$statement = $mysqli->prepare("UPDATE users SET firstname=? WHERE email=?");
		$statement->bind_param('ss', $firstname, filter_var($_SESSION['email']));
		$statement->execute();
		$_SESSION['name'] = explode(' ', $_SESSION['name'])[0] . ' ' . $firstname;
	  }
	  if(isset($_POST['lastname'])) {
		$lastname = filter_input(INPUT_POST, 'lastname');
		$statement = $mysqli->prepare("UPDATE users SET lastname=? WHERE email=?");
		$statement->bind_param('ss', $lastname, filter_var($_SESSION['email']));
		$statement->execute();
		$_SESSION['name'] = $lastname . ' ' . explode(' ', $_SESSION['name'])[1];
	  }
	  if(isset($_POST['address'])) {
		$address = filter_input(INPUT_POST, 'address');
		$statement = $mysqli->prepare("UPDATE users SET address=? WHERE email=?");
		$statement->bind_param('ss', $address, filter_var($_SESSION['email']));
		$statement->execute();
		$_SESSION['address'] = $address;
	  }
	  if(isset($_POST['phone'])) {
		$phone = filter_input(INPUT_POST, 'phone');
		$statement = $mysqli->prepare("UPDATE users SET phone=? WHERE email=?");
		$statement->bind_param('ss', $phone, filter_var($_SESSION['email']));
		$statement->execute();
		$_SESSION['phone'] = $phone;
	  }
	}
}


?>
<section class="user-dashboard page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <ul class="list-inline dashboard-menu text-center">
          <li><a class="active" href="/profile">Profile Details</a><a href="/orders">Orders</a></li>
        </ul>
        <?php if($error): ?>
            <div>
                <div>
                    <div class="alertPart">
                    <div class="alert alert-danger errreg" role="alert"><i class="tf-ion-close-circled"></i>The following errors have occurred! <ul><?php foreach($errormsg as $msg){echo '<li>'.$msg.'<li>';} ?></ul>
                    </div>
                </div>		
            </div>
        <?php endif ?>
        <div class="dashboard-wrapper dashboard-user-profile">
            <div class="media">
              <div class="media-body">
                <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="requires-validation" novalidate>
                  <ul class="user-profile-list">
                    <?= CSRF::csrfInputField() ?>
                    <li><span>Firstname:</span><div class="text-center"><input minlength="2" type="text" class="form-control <?= $state[0] ?>" name="firstname" placeholder="John" value="<?= explode(' ', $_SESSION['name'])[1] ?>" required></div></li>
                    <li><span>Lastname:</span><div class="text-center"><input type="text" minlength="2" class="form-control <?= $state[1] ?>" name="lastname" placeholder="Doe" value="<?= explode(' ', $_SESSION['name'])[0] ?>" required></div></li>
                    <li><span>Address:</span><div class="text-center"><input type="text" minlength="15" class="form-control <?= $state[3] ?>" name="address" placeholder="Street number, Street name, " value="<?= $_SESSION['address'] ?>" required></div></li>
                    <li><span>Phone:</span><div class="text-center"><input type="tel" minlength="10" class="form-control <?= $state[2] ?>" name="phone" placeholder="905-123-4567" value="<?= $_SESSION['phone'] ?>" required></div></li>
                    <li><div class="text-center"><button class="btn btn-main" type="submit" name="update">Update</button></div></li>
                  </ul>
                </form>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>
</section>
	<!-- Validator -->
    <script src="/js/validator.js"></script>
<?php require __DIR__ . '/footer.php'; ?>