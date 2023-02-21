<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/db.php';

if(!isset($_SESSION['name'])) {
  header('Location: /login');
}

$orders;
$statement = $pdo->prepare("SELECT * FROM transactions WHERE email=? ORDER BY id DESC");
$statement->execute(array($_SESSION['email']));
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement = $pdo->prepare("SELECT * FROM app_settings WHERE name=?");
$statement->execute(array('tax_rates'));
if($statement->rowCount() > 0) {
	$tax_rates = $statement->fetchAll();
	if (empty($tax_rates[0]['value'])){
		$tax_rates = 0;
	}
}

?>
<section class="user-dashboard page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<ul class="list-inline dashboard-menu text-center">
                    <li><a href="/profile">Profile Details</a><a class="active" href="/orders">Orders</a></li>
				</ul>
				<div class="dashboard-wrapper user-dashboard">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Price</th>
                                <th>Payment ID</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                <td><?= $order['timestamp'] ?></td>
                                <td>$<?php
                                    $details = unserialize($order['details']);
                                    $total = 0;
                                    foreach($details as $detail) {
                                        $total += $detail['price'] * $detail['quantity'];
                                    }
									$taxed = 0;
									foreach(explode(',', $tax_rates[0]['value']) as $rate) {
										$taxed += tax($total, $rate);
									}
									echo number_format(bcadd($total, $taxed, 2), 2);
                                    ?>
                                </td>
								<td><?= $order['payment_id'] ?></td>
								<td><?= $order['readable_orderstatus'] ?></td>
                                <td><a href="/order-details?id=<?= $order['id'] ?>" class="btn-default">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php require __DIR__ . '/footer.php'; ?>