<?php

require __DIR__ . '/../db.php';
$config = parse_ini_file(__DIR__ . '/config/config.ini');
require $config['bt_autoloader'];
$gateway = new Braintree\Gateway([
    'environment' => $config['environment'],
    'merchantId' => $config['merchantid'],
    'publicKey' => $config['public_key'],
    'privateKey' => $config['public_key']
]);
$transactionPendingStatuses = [
    Braintree\Transaction::AUTHORIZED,
    Braintree\Transaction::AUTHORIZING,
    Braintree\Transaction::SETTLING,
    Braintree\Transaction::SETTLEMENT_CONFIRMED,
    Braintree\Transaction::SETTLEMENT_PENDING,
    Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT
];
// ============== UPDATE STATUS FROM API ================ //
$statement = $pdo->prepare("SELECT * FROM transactions WHERE payment_status !=?");
$statement->execute(array('settled'));
if($statement->rowCount() > 0) {
    $transactions = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach ($transactions as $t) {
		$transaction = $gateway->transaction()->find($t["payment_id"]);
		$status = $transaction->status;
		$statement = $pdo->prepare("UPDATE transactions SET payment_status=? WHERE payment_id=?");
		$statement->execute(array($status, $t["payment_id"]));
		
	}
}
// =============== UPDATE READABLE STATUS =================== //
$statement = $pdo->prepare("SELECT * FROM transactions WHERE readable_orderstatus IS NULL OR readable_orderstatus != ?");
$statement->execute(array('Paid'));
if($statement->rowCount() > 0) {
	$transaction = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach ( $transaction as $t ) {
	  echo 'start foreach'."\n";
		if (in_array($t['payment_status'], $transactionPendingStatuses)) {
			$statement = $pdo->prepare("UPDATE transactions SET readable_orderstatus=? WHERE payment_id=?");
			$statement->execute(array('Pending', $t['payment_id']));
			echo 'Marked ' . $t['payment_id'] .' Pending'."\n";
			echo 'Processed ' . $t["payment_id"]. ' now: Pending was:' . $t['payment_status'] . "\n";
		} elseif ($t['payment_status'] == 'settled') {
			$statement = $pdo->prepare("UPDATE transactions SET readable_orderstatus=? WHERE payment_id=?");
			$statement->execute(array('Paid', $t['payment_id']));
			echo 'Marked ' . $t['payment_id'] .' Paid'."\n";
			echo 'Processed ' . $t["payment_id"]. ' now: settled was:' . $t['payment_status'] . "\n";
		} else {
			$statement = $pdo->prepare("UPDATE transactions SET readable_orderstatus=? WHERE payment_id=?");
			$statement->execute(array('Payment Error', $t['payment_id']));
			echo 'Marked ' . $t['payment_id'] .' Payment Error'."\n";
			echo 'Processed ' . $t["payment_id"]. ' now: Error! was:' . $t['payment_status'] . "\n";
		}
	}
}

?>