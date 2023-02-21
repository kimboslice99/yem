<?php 

require __DIR__ . '/header.php'; 
require __DIR__ . '/db.php'; 

$statement = $pdo->prepare("SELECT * FROM faq");
$statement->execute();

$statement = $pdo->prepare("SELECT * FROM contact WHERE name=?");
$statement->execute(array('email'));
$email = $statement->fetchAll();
?>

<section class="page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<h2>Frequently Asked Questions</h2>
				<?php if(!empty($email[0]['value'])): ?>
				<p><?= $email[0]['value'] ?></p>
				<?php endif ?>
			</div>
			<div class="col-md-8">
				<?php if($statement->rowCount() > 0): $faq = $statement->fetchAll(PDO::FETCH_ASSOC);?>
					<?php foreach($faq as $data): ?>
						<h4><?= $data['question'] ?></h4>
						<p><?= $data['answer'] ?></p>
					<?php endforeach; ?>
				<?php endif ?>
			</div>
		</div>
	</div>
</section>

<?php require __DIR__ . '/footer.php'; ?>