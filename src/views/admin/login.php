<?php

require __DIR__ . '/../db.php';
require __DIR__ . '/../../csrf.php';
require __DIR__ . '/../abuseipdb.php';

$error = false;

if(isset($_POST['submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
    $username = filter_input(INPUT_POST, 'username');
    $password = filter_input(INPUT_POST, 'password');
    $statement = $pdo->prepare("SELECT * FROM admin WHERE username=?");
    $statement->execute(array($username));
    if($statement->rowCount() > 0) {
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(password_verify($password, $result[0]['password'])) {
            $_SESSION['admin'] = 'admin';
            header('Location: /admin/home');
        }else{
			$error = true;
		}
    }else{
		$error = true;
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $config['title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="/a/css/auth.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <div class="auth-content">
            <div class="card">
                <div class="card-body text-center">
                    <?php if($error === true):
file_put_contents(__DIR__ . '/../bin/failed_admin.txt', date('M/d/Y H:m:s') . ' Login Failed! ' . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND|LOCK_EX); ?>
                        <div class="alert alert-danger" role="alert">Login Failed, Incorrent Username/Password</div>
                    <?php endif ?>
                    <h6 class="mb-4 text-muted">Login to your account</h6>
                    <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="requires-validation" novalidate>
                        <?= CSRF::csrfInputField() ?>
                        <div class="mb-3 text-start">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="mb-3 text-start">
                            <div class="form-check">
                              <input class="form-check-input" name="remember" type="checkbox" value="" id="check1">
                              <label class="form-check-label" for="check1">
                                Remember me on this device
                              </label>
                            </div>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary shadow-2 mb-4">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- Popper 2.11.6 -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- Bootstrap 5.3.0 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <script src="/js/validator.js"></script>
</body>

</html>