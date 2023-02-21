<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/../csrf.php';
require __DIR__ . '/db.php';
require __DIR__ . '/admin/util.php';
require __DIR__ . '/abuseipdb.php';

$errormsg = array();
$success = $error = false;
$state[0] = $state[1] = $state[2] = $state[3] = $state[4] = $state[5] = $name = $email = $phone = $message = '';
if(isset($_POST['contact']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))){
	if(strlen($_POST['name']) < 2) { $error = true && array_push($errormsg, 'Name must be at least two characters<br>') && $state[0] = 'is-invalid' ; }
	if(strlen($_POST['email']) < 4) { $error = true && array_push($errormsg, 'E-Mail must be at least 5 characters<br>') && $state[1] = 'is-invalid' ; }
	if(strlen(preg_replace('/[^0-9]/', '', $_POST['phone'])) < 10) { $error = true && array_push($errormsg, 'Phone must be at least 10 digits<br>') && $state[2] = 'is-invalid' ; }
	if(strlen($_POST['message']) < 10) { $error = true && array_push($errormsg, 'Message must be at least 10 characters<br>') && $state[3] = 'is-invalid' ; }
	if(AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) { $error = true ; }
	$name = filter_input(INPUT_POST, 'name');
	$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$phone = filter_input(INPUT_POST, 'phone');
	$message = filter_input(INPUT_POST, 'message');
	if(!$error) {
	if(sendEmail(array($config['mail_to_address']), "Contact form", "New message from $name\n\n$message\n\nPhone: $phone\n\nRemote Address: ".$_SERVER['REMOTE_ADDR'], false, null)) {
		$success = true;
		$name = $email = $phone = $message = '';
		} else {
			$error = true && array_push($errormsg, 'Backend errors, Sorry!');
			}
	}
}
?>
<section>
    <div>
        <div class="div-center">
        <?php if($error): ?>
            <div>
                <div>
                    <div class="alertPart">
                    <div class="alert alert-danger errreg" role="alert"><span>Submission Failed! </span>The following errors have occurred! <ul><?php foreach($errormsg as $msg){echo '<li>'.$msg.'<li>';} ?></ul>
                    </div>
                </div>		
            </div>
		<?php elseif($success): ?>
			<div>
				<div>
					<div class="alertPart">
					<div class="alert alert-success errreg" role="alert"><span>Thanks for contacting us! We'll respond at our earliest convenience</span>
					</div>
				</div>
			</div>
		<?php endif ?>
			<div class="block text-center">
				<div>
					<h2>Contact</h2>
				</div>
				<form class="text-left clearfix requires-validation" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" novalidate>
					<?= CSRF::csrfInputField() ?>
					<div class="form-group p-1">
						<input type="text" name="name" class="form-control <?= $state[0] ?>" value="<?= $name ?>" placeholder="Name" required>
						  <div class="valid-feedback font-10">
							 Name looks good!
						  </div>
						  <div class="invalid-feedback font-10">
							 Name is required!
						  </div>
							</div>
					<div class="form-group p-1">
						<input type="email" name="email" class="form-control <?= $state[1] ?>" value="<?= $email ?>"  placeholder="Email" required>
						  <div class="valid-feedback font-10">
							 E-Mail looks good!
						  </div>
						  <div class="invalid-feedback font-10">
							 E-Mail is required!
						  </div>
					</div>
					<div class="form-group p-1">
						<input type="tel" name="phone" class="form-control <?= $state[2] ?>" value="<?= $phone ?>"  placeholder="Phone" required>
						  <div class="valid-feedback font-10">
							 Phone looks good!
						  </div>
						  <div class="invalid-feedback font-10">
							 Phone is required!
						  </div>
					</div>
					<div class="form-group p-1">
						<textarea type="message" name="message" class="font-12 form-control <?= $state[3] ?>" value="<?= $message ?>"  placeholder="Message" required><?= $message ?></textarea>
						  <div class="valid-feedback font-10">
							 Message looks good!
						  </div>
						  <div class="invalid-feedback font-10">
							 Message is required!
						  </div>
					</div>
					<div class="text-center p-1">
						<button name="contact" type="submit" class="btn btn-main text-center" >Submit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

	<!-- Validator -->
    <script src="/js/validator.js"></script>
<?php require __DIR__ . '/footer.php'; ?>