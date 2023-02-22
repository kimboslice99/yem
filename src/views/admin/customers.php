<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/../mysqli.php';
require __DIR__ . '/../../csrf.php';
require __DIR__ . '/../abuseipdb.php';

$customers = array();
$edit = false;

if(isset($_POST['submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    if(isset($_POST['firstname'])) {
		$f = filter_input(INPUT_POST, 'firstname');
        $statement = $mysqli->prepare("UPDATE users SET firstname=? WHERE id=?");
		$statement->bind_param('si', $f, $id);
        $statement->execute();
    }
    if(isset($_POST['lastname'])) {
		$l = filter_input(INPUT_POST, 'lastname');
        $statement = $pdo->prepare("UPDATE users SET lastname=? WHERE id=?");
		$statement->bind_param('si', $l, $id);
        $statement->execute();
    }
    if(isset($_POST['phone'])) {
		$p = filter_input(INPUT_POST, 'phone');
        $statement = $mysqli->prepare("UPDATE users SET phone=? WHERE id=?");
		$statement->bind_param('si', $p, $id);
        $statement->execute();
    }
    if(isset($_POST['address'])) {
		$a = filter_input(INPUT_POST, 'address');
        $statement = $mysqli->prepare("UPDATE users SET address=? WHERE id=?");
		$statement->bind_param('si', $a, $id);
        $statement->execute();
    }
    if(isset($_POST['email'])) {
		$e = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $statement = $mysqli->prepare("UPDATE users SET email=? WHERE id=?");
		$statement->bind_param('si', $e, $id);
        $statement->execute();
    }
    if(isset($_POST['password'])) {
		$p = password_hash(filter_input(INPUT_POST, 'password'));
        $statement = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
		$statement->bind_param('si', $p, $id);
        $statement->execute();
    }
}

if(isset($_GET['id'])) {
    $edit = true;
	$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $statement = $mysqli->prepare("SELECT * FROM users WHERE id=?");
	$statement->bind_param('i', $id);
    $statement->execute();
    $result = $statement->get_result();
    if($statement->affected_rows > 0) {
		$customers = $result->fetch_assoc();
    }
} else {
    if(isset($_POST['delete']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $statement = $mysqli->prepare("DELETE FROM users WHERE id=?");
		$statement->bind_param('i', $id);
        $statement->execute();
    }
    
    $statement = $mysqli->prepare("SELECT * FROM users");
    $statement->execute();
    $result = $statement->get_result();
    if($statement->affected_rows > 0) {
		while($assoc = $result->fetch_assoc()){
			array_push($customers, $assoc);
		}
    }
}
$csrf = CSRF::csrfInputField();
?>
<div class="container">
    <div class="page-title">
        <h3>Customers
        <a href="/admin/customers/create" class="btn btn-sm btn-outline-primary float-end"><i class="fas fa-plus"></i> Add</a>
        </h3>
    </div>
    <?php if($edit): ?>
        <div class="card">
            <div class="card-header">Edit Customer</div>
            <div class="card-body">
                <form accept-charset="utf-8" method="post" action="/admin/customers">
                    <?= $csrf ?>
                    <div class="row g-2">
                        <div class="mb-3 col-md-4">
                            <input type="text" name="firstname" class="form-control" placeholder="Firstname" value="<?= $customers['firstname'] ?>">
                        </div>
                        <div class="mb-3 col-md-4">
                            <input type="text" name="lastname" class="form-control" placeholder="Lastname" value="<?= $customers['lastname'] ?>">
                        </div>
                        <div class="mb-3 col-md-4">
                            <input type="tel" name="phone" class="form-control" placeholder="Phone" value="<?= $customers['phone'] ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="address" placeholder="Address" value="<?= $customers['address'] ?>">
                    </div>
                    <div class="row g-2">
                        <div class="mb-3 col-md-6">
                            <input type="email" class="form-control" name="email" placeholder="Email" value="<?= $customers['email'] ?>">
                        </div>
                        <div class="mb-3 col-md-6">
                            <input type="password" class="form-control" name="password" placeholder="Password">
                        </div>
                    </div>
                    <input type="text" name="id" value="<?= $customers['id'] ?>" hidden>
                    <button name="submit" type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="box box-primary">
            <div class="box-body">
                <table width="100%" class="table table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($customers)): ?>
                            <?php foreach($customers as $customer): ?>
                                <tr>
                                    <td><?= $customer['lastname'] . ' ' . $customer['firstname'] ?></td>
                                    <td><?= $customer['email'] ?></td>
                                    <td><?= $customer['phone'] ?></td>
                                    <td><?= $customer['address'] ?></td>
                                    <td class="text-end">
                                        <form action="/admin/customers" method="post">
                                            <?= $csrf ?>
                                            <input type="text" name="id" value="<?= $customer['id'] ?>" hidden>
                                            <a href="/admin/customers?id=<?= $customer['id']; ?>" class="btn btn-outline-info btn-rounded"><i class="fas fa-pen"></i></a>
                                            <button name="delete" type="submit" class="btn btn-outline-danger btn-rounded"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>