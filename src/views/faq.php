<?php 

require __DIR__ . '/header.php'; 
require __DIR__ . '/mysqli.php'; 

$faq = array();
$statement = $mysqli->prepare("SELECT * FROM faq");
$statement->execute();
$result = $statement->get_result();
while($assoc = $result->fetch_assoc()){
	array_push($faq, $assoc);
}
?>

<section class="page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<h2>Frequently Asked Questions</h2>
				<?php if(!empty($config['contact_email'])): ?>
				<p><?= $config['contact_email'] ?></p>
				<?php endif ?>
			</div>
			<div class="col-md-8">
				<?php if($statement->affected_rows > 0):?>
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