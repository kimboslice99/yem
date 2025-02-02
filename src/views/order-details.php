<?php

require __DIR__ . '/header.php';
require __DIR__ . '/mysqli.php';

if(!isset($_SESSION['name'])) {
    header('Location: /login');
}

if(!isset($_GET['id'])) {
    header('Location: /profile');
}

$details;
$statement = $mysqli->prepare("SELECT * FROM transactions WHERE id=? AND email=?");
$statement->bind_param('is', filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT), $_SESSION['email']);
$statement->execute();
$result = $statement->get_result();
if($statement->affected_rows > 0) {
	$assoc = $result->fetch_assoc();
    $details = unserialize($assoc['details']);
    $payment_details = unserialize($assoc['payment_details']);
}
if(empty($config['tax'])){
	$tax = 0;
}else{
	$tax = $config['tax'];
}
$c = $config['currency_symbol'];
?>

<?php if(isset($details)): ?>
    <section class="user-dashboard page-wrapper">
    	<div class="container">
		    <div class="row">
            <div class="dashboard-wrapper user-dashboard">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th>Item Name</th>
									<th>Item Price</th>
									<th>Qty</th>
									<th>Sub Total</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($details as $detail): ?>
                                    <tr>
                                        <td><a href="/item?id=<?= $detail['id'] ?>"><?= $detail['title'] ?></a></td>
                                        <td><?= $c.number_format($detail['price'], 2) ?></td>
                                        <td><?= $detail['quantity'] ?></td>
                                        <td><?= $c.number_format($detail['price'] * $detail['quantity'], 2) ?></td>
                                        <td><?php
                                        $total = $detail['price'] * $detail['quantity'];
										$taxed = 0;
										foreach(explode(',', $tax) as $rate) {
											$taxed += tax($total, $rate);
										}
										echo $c.number_format(bcadd($total, $taxed, 2), 2);
                                    ?></td>
                                    </tr>
                                <?php endforeach; ?>
								<tr>
									<td>Shipping</td>
									<td></td>
									<td></td>
									<td></td>
									<td><?= $c.number_format($payment_details["shippingcost"], 2); ?></td>
								</tr>
                                <tr>
									<td><b>Total</b></td>
									<td></td>
									<td></td>
									<td><b><?php
                                        $total = 0;
                                        foreach($details as $detail) {
                                            $total += $detail['price'] * $detail['quantity'];
                                        }
                                        echo $c.number_format($total, 2);
                                    ?></b>
                                    </td>
									<td><b><?php
                                        $total = 0;
                                        foreach($details as $detail) {
                                            $total += $detail['price'] * $detail['quantity'];
                                        }
										$taxed = 0;
										foreach(explode(',', $tax) as $rate) {
											$taxed += tax($total, $rate);
										}
										echo $c.number_format(bcadd(bcadd($total, $payment_details["shippingcost"], 2), $taxed, 2), 2);
                                    ?></b>
                                    </td>
								</tr>
							</tbody>
						</table>
						<table class="table">
							<thead>
								<tr>
									<th><p>Shipping Details</p></th>
									<th></th>
									<th><p>Payment Status</p></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?= $payment_details['method'] . ' ' . $payment_details["shippingcost"] ?></td>
									<td></td>
									<td><?= $assoc['readable_orderstatus'] ?></td>
								</tr>
							</tbody>
						</table>
						</div>
					</div>
				</div>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="empty-cart page-wrapper">
        <div class="container">
            <div class="row">
            <div>
                <div class="block text-center">
                    <h2 class="text-center">Transaction not found.</h2>
            </div>
            </div>
        </div>
    </section>
<?php endif ?>

<?php require __DIR__ . '/footer.php'; ?>