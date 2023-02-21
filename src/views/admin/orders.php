<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/../db.php';
require __DIR__ . '/util.php';
require __DIR__ . '/../../csrf.php';
if (
	empty($config['environment']) ||
	empty($config['merchantid']) ||
	empty($config['public_key']) ||
	empty($config['private_key'])
	){ 
		$bt = false; 
	} else {
		require __DIR__ . '/../bin/bt/lib/autoload.php';
		$gateway = new Braintree\Gateway([
			'environment' => $config['environment'],
			'merchantId' => $config['merchantid'],
			'publicKey' => $config['public_key'],
			'privateKey' => $config['private_key']
		]);
	}
if(isset($_POST['check']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
  $transaction = $gateway->transaction()->find(filter_input(INPUT_POST, "id"));
  $status = $transaction->status;
  $statement = $pdo->prepare("UPDATE transactions SET payment_status=? WHERE payment_id=?");
  $statement->execute(array(filter_var($status), filter_input(INPUT_POST, 'id')));
}

$transactions;

$statement = $pdo->prepare("SELECT * FROM transactions ORDER BY id DESC");
$statement->execute();
if($statement->rowCount() > 0) {
    $transactions = $statement->fetchAll(PDO::FETCH_ASSOC);
}
if(empty($config['tax'])){
	$tax = 0;
}else{
	$tax = $config['tax'];
}
$csrf = CSRF::csrfInputField();
?>
<div class="container">
    <div class="page-title">
        <h3>Orders</h3>
    </div>
    <div class="box box-primary">
        <div class="box-body">
            <table width="100%" class="table table-hover" id="dataTables-example">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Details</th>
                        <th>Payment Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($transactions)): ?>
                        <?php foreach($transactions as $transaction): ?>
                            <tr>
                                <td><?= $transaction['name'] ?></td>
                                <td><?= $transaction['email'] ?></td>
                                <td><?= $transaction['address'] ?></td>
                                <td>
                                    <table width="100%" class="table table-hover" id="dataTables-example">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Sub-Total</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $details = unserialize($transaction['details']);
                                                $total = 0;
												$taxed = 0;
                                                $subtotal = 0;
                                                foreach($details as $detail) {
                                                    echo '<tr>';
                                                    echo '<td>' . $detail['title'] . '</td>';
                                                    echo '<td>$' . number_format($detail['price'], 2) . '</td>';
                                                    echo '<td>' . $detail['quantity'] . '</td>';
                                                    echo '<td>$' . number_format($detail['price'] * $detail['quantity'], 2) . '</td>';
                                                    echo '<td>$' . number_format($detail['price'] * $detail['quantity'] + $detail['price'] * $detail['quantity'] * 5 / 100 + $detail['price'] * $detail['quantity'] * 8 / 100, 2) . '</td>';
                                                    echo '</tr>';
													$subtotal += $detail['price'] * $detail['quantity'];
                                                    $total += $detail['price'] * $detail['quantity'];
													foreach(explode(',', $tax) as $rate) {
														$taxed += tax($total, $rate);
													}
                                                }
                                                echo '<tr>';
                                                echo '<td>Total</td>';
                                                echo '<td></td>';
                                                echo '<td></td>';
                                                echo '<td>$' . number_format($subtotal, 2) . '</td>';
                                                echo '<td>$' . number_format($taxed, 2) . '</td>';
                                                echo '</tr>';
                                            ?>
                                        </tbody>
                                    </table>
                                </td>
                                <td><?= $transaction['payment_status'] ?><br><?= $transaction['payment_id'] ?><form method="post" action="" name="check"><?= $csrf ?><input type="hidden" name="id" value="<?= $transaction['payment_id'] ?>"><button class="btn btn-dark" name="check" type="submit">Check Status</button></form></td>
                                <td><?= $transaction['timestamp'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>