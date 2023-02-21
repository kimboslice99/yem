<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/mysqli.php';

if(!isset($_SESSION['name'])) {
  header('Location: /login');
}
// create array to push to
$orders = array();
$statement = $mysqli->prepare("SELECT * FROM transactions WHERE email=? ORDER BY id DESC");
$statement->bind_param('s', filter_var($_SESSION['email']));
$statement->execute();
$result = $statement->get_result();
while($assoc = $result->fetch_assoc()){
	array_push($orders, $assoc);
}
if(empty($config['tax'])){
	$tax = 0;
}else{
	$tax = $config['tax'];
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
									foreach(explode(',', $tax) as $rate) {
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