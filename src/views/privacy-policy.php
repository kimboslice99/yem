<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/mysqli.php';

$statement = $mysqli->prepare("SELECT * FROM policy");
$statement->execute();
$result = $statement->get_result();
$data = $result->fetch_assoc();

?>
<section class="about section">
    <div class="container">
		<div class="row">
			<div class="col-lg-5-offset-3 col-md-5 col-sm-12">
				<h2>Privacy Policy</h2>
				<p><?= $data['policy'] ?></p>
			</div>
		</div>
    </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>