<?php

require __DIR__ . '/../mysqli.php';

$days = array();
$orders = array();
$revenue = array();

$now = new DateTime("7 days ago");
$interval = new DateInterval('P1D');
$period = new DatePeriod($now, $interval, 7);
foreach($period as $day) {
    $days[] = $day->format('M d');
}


for($i = 6; $i >= 0; $i--) {
    $time = time();
    $dateRange = array(
        gmdate('Y-m-d', $time - ($i * 24 * 60 * 60)) . ' 00:00:00 GMT',
        gmdate('Y-m-d', $time - ($i * 24 * 60 * 60)) . ' 22:59:59 GMT',
    );
    $statement = $mysqli->query("SELECT count(*) FROM transactions WHERE timestamp >= '$dateRange[0]' AND timestamp <= '$dateRange[1]'");
    $orders[] = (int)$statement->fetch_assoc()["count(*)"]; // specify INT and php will leave count(*) unquoted
    $transactions = $mysqli->query("SELECT * FROM transactions WHERE timestamp >= '$dateRange[0]' AND timestamp <= '$dateRange[1]'");
    $transactionRevenue = 0;
    foreach($transactions as $transaction) {
        $details = unserialize($transaction['details']);
        foreach($details as $detail) {
            $transactionRevenue += $detail['price'] * $detail['quantity'];
        }
    }
    $revenue[] = $transactionRevenue;
}

echo json_encode(array($days, $orders, $revenue));
