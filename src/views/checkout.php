<?php

ob_start();
require __DIR__ . '/../csrf.php';
require __DIR__ . '/abuseipdb.php';
require __DIR__ . '/db.php';
require __DIR__ . '/invoice.php';
require __DIR__ . '/admin/util.php';
// ======== CHECK THAT WE HAVE FILLED IN BT DETAILS ======== //
if (
	empty($config['environment']) ||
	empty($config['merchantid']) ||
	empty($config['public_key']) ||
	empty($config['private_key']) ||
	empty($config['bt_autoloader'])
	)
	{
		die(header('Location: /'));
	}
require $config['bt_autoloader'];
$error = false;
// ======================== BRAINTREE ====================== //
$gateway = new Braintree\Gateway([
    'environment' => $config['environment'],
    'merchantId' => $config['merchantid'],
    'publicKey' => $config['public_key'],
    'privateKey' => $config['private_key']
]);
// =================== CALCULATE TOTAL =================== //
$total = 0;
foreach($_SESSION['cart'] as $item) {
	$statement = $pdo->prepare("SELECT * FROM products WHERE id=?");
	$statement->execute(array($item['id']));
	if($statement->rowCount() > 0) {
		$db = $statement->fetchAll(PDO::FETCH_ASSOC);
		$total += $db[0]['price'] * $item['quantity'];
	}
}
// ============= IF CART EMPTY ========== //
if($total == 0 && empty($_SESSION['method']) || empty($_SESSION['shipping'])) { header('Location: /'); }
// ============= TAXES =============== //
if (empty($config['tax'])){
	$tax_rates = 0;
}
foreach(explode(',', $config['tax']) as $rate) {
	$taxed += tax($total, $rate);
}
$total = bcadd($total, $taxed, 2);
// ============== IF SUBMIT ================== //
if(isset($_POST['payment_method_nonce']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))){
// ============= CREATE SALE ============== // 
$result = $gateway->transaction()->sale([
  'amount' => bcadd($total, filter_var($_SESSION['shipping']), 2),
  'paymentMethodNonce' => filter_input( INPUT_POST, 'payment_method_nonce' ),
  'deviceData' => $_POST['data'],
  'customer' => [
        'id' => filter_var($_SESSION['id']),
        'firstName' => filter_var($_SESSION['firstname']),
        'lastName' => filter_var($_SESSION['lastname']),
        'email' => filter_var($_SESSION['email']),
        'phone' => filter_var($_SESSION['phone'])
    ], 
  'options' => [
    'submitForSettlement' => True
  ]
]);
$transaction = $result->transaction;
// =============== VALID SUCCESS STATUSES? ================ //
$transactionSuccessStatuses = [
    Braintree\Transaction::AUTHORIZED,
    Braintree\Transaction::AUTHORIZING,
    Braintree\Transaction::SETTLED,
    Braintree\Transaction::SETTLING,
    Braintree\Transaction::SETTLEMENT_CONFIRMED,
    Braintree\Transaction::SETTLEMENT_PENDING,
    Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT
];
// =================== CATCH ERRORS ============== //
if (!$result->success) {
file_put_contents(__DIR__ . '/bin/payment_error.log', date('Y-m-d H:i:s') . ' ' . $transaction->status . ' ' . $_SESSION['name'] . '@' . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND|LOCK_EX);
die(json_encode(array(
	'status' => $transaction->status,
	// "processor_declined"
	'processorResponseType' => $transaction->processorResponseType,
	// "soft_declined"
	'processorResponseCode' => $transaction->processorResponseCode,
	// "2000"
	'processorResponseText' => $transaction->processorResponseText))); }


if (!$result->transaction) {
die(json_encode(array(
	'status' => $transaction->status,
	// "processor_declined"
	'processorResponseType' => $transaction->processorResponseType,
	// "soft_declined"
	'processorResponseCode' => $transaction->processorResponseCode,
	// "2000"
	'processorResponseText' => $transaction->processorResponseText))); }


if (!in_array($transaction->status, $transactionSuccessStatuses)) {
	$errorString = "";
	foreach($result->errors->deepAll() as $error) {
		$errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
	}
	file_put_contents(__DIR__ . '/bin/payment_error.log', date('Y-m-d H:i:s') . ' ' . $errorString . ' ' . $_SESSION['name'] . '@' . $_SERVER['REMOTE_ADDR'] ."\n", FILE_APPEND|LOCK_EX);
	die(json_encode(array(
		'status' => 'Payment error',
		
		'processorResponseType' => $transaction->processorResponseType,
		// "soft_declined"
		'processorResponseCode' => $transaction->processorResponseCode,
		// "2000"
		'processorResponseText' => $transaction->processorResponseText))) ; }

if ($result->success && $result->transaction) {
    $transaction = $result->transaction;
		$paymentdetails = array();
		$id = $transaction->id;
		$amount = $transaction->amount;
		$status = $transaction->status;
		$paymentdetails['type'] = $transaction->type;
		$paymentdetails['createdAt'] = $transaction->createdAt->format('Y-m-d H:i:s');
		$paymentdetails['updatedAt'] = $transaction->updatedAt->format('Y-m-d H:i:s');
		$paymentdetails['token'] = $transaction->creditCardDetails->token;
		$paymentdetails['bin'] = $transaction->creditCardDetails->bin;
		$paymentdetails['last4'] = $transaction->creditCardDetails->last4;
		$paymentdetails['cardType'] = $transaction->creditCardDetails->cardType;
		$paymentdetails['expirationDate'] = $transaction->creditCardDetails->expirationDate;
		$paymentdetails['cardholderName'] = $transaction->creditCardDetails->cardholderName;
		$paymentdetails['customerLocation'] = $transaction->creditCardDetails->customerLocation;
		$paymentdetails['id'] = $transaction->customerDetails->id;
		$paymentdetails['firstname'] = $transaction->customerDetails->firstName;
		$paymentdetails['lastname'] = $transaction->customerDetails->lastName;
		$paymentdetails['email'] = $transaction->customerDetails->email;
		$paymentdetails['phone'] = $transaction->customerDetails->phone;
		$paymentdetails['processorResponseCode'] = $result->transaction->processorResponseCode;
		$paymentdetails['shippingcost'] = filter_var($_SESSION['shipping']);
		$paymentdetails['method'] = filter_var($_SESSION['method']);
		// ================ INSERT DETAILS INTO DATABASE ======================== //
		$serialize = serialize($paymentdetails);
		$details = serialize($_SESSION['cart']);
		$timestamp = gmdate('Y-m-d h:i:s');
		$readable = 'Pending';
		$statement = $pdo->prepare("INSERT INTO transactions (name, email, address, details, timestamp, payment_status, payment_amount, payment_id, readable_orderstatus, payment_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$statement->execute(array(filter_var($_SESSION['name']), filter_var($_SESSION['email']), filter_var($_SESSION['address']), $details, $timestamp, $status, $amount, $id, $readable, $serialize));
		$message = generateInvoice($timestamp);
		$image['name'] = 'logo.jpg';
		$image['path'] = __DIR__ . '/images/logo.jpg';
		// ================ SEND INVOICE EMAIL ============= //
		sendEmail(array($_SESSION['email']), 'Invoice', $message, true, null, $image);
		file_put_contents(__DIR__ . '/bin/payment_error.log', date('Y-m-d H:i:s') . ' Payment '. $transaction->status . '! ' . $_SESSION['name'] . '@' . $_SERVER['REMOTE_ADDR'] ."\n", FILE_APPEND|LOCK_EX);
		foreach($_SESSION['cart'] as $item){
			//==================== UPDATE STOCK =================== //
			$statement = $pdo->prepare("UPDATE products SET qty=(SELECT qty - (?) FROM products WHERE id=?) WHERE id=?");
			$statement->execute(array($item['quantity'], $item['id'], $item['id']));
		}
		// === CLEAR CART AFTER === //
		unset($_SESSION['cart']);
		unset($_SESSION['method']);
		unset($_SESSION['shipping']);
		
		$array = array(
		'id' => $transaction->id,
		// 123
		'status' => 'success',
		// "processor_declined"
		'processorResponseType' => $transaction->processorResponseType,
		// "soft_declined"
		'processorResponseCode' => $transaction->processorResponseCode,
		// "2000"
		'processorResponseText' => $transaction->processorResponseText);
		// "Do Not Honor"
		echo json_encode($array);
		die();
		
}
}
// === nonce for csp - is this enough? === //
$nonce = bin2hex(openssl_random_pseudo_bytes(128));
// ===== CSP ==== //
header("Content-Security-Policy: default-src 'none';style-src 'self' https://fonts.googleapis.com/css https://assets.braintreegateway.com/;connect-src 'self' api.sandbox.braintreegateway.com client-analytics.sandbox.braintreegateway.com *.braintree-api.com;img-src 'self' data: https://c.sandbox.paypal.com/ https://slc2.stats.paypal.com https://b.stats.paypal.com assets.braintreegateway.com;script-src-elem 'self' 'nonce-".$nonce."' https://c.paypal.com/ https://js.braintreegateway.com/;frame-src 'self' https://c.sandbox.paypal.com/ https://assets.braintreegateway.com/;child-src assets.braintreegateway.com;font-src 'self' https://fonts.gstatic.com;report-uri /csp/report;");
?>
<head>
<title>&lrm;</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="/css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
</head>
<body>
<div class="box">
  <div class="ribbon ribbon-top-left"><a href="<?= $_SERVER['HTTP_REFERER'] ?>"><span>Back</span></div></a>
</div>
<div class="div-center">
		<div>
			<!-- Site Logo -->
			<div class="logo text-center">
				<a href="/">
				<img class="logo-h" src="/images/logo.jpg" alt="Logo">
				</a>
			</div>
		</div>
            <div>
                <div>
                    <div id="id" class="alert" hidden>
                    <div class="errreg" role="alert">Transaction Failed!<br><span id="message"></span></div>
                    </div>
                </div>		
            </div>
		  <div class="preloader">
			<svg mlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-ripple svg"><circle cx="50" cy="50" r="4.719" fill="none" stroke="#1d3f72" stroke-width="2"><animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="3" keySplines="0 0.2 0.8 1" begin="-1.5s" repeatCount="indefinite"/><animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="3" keySplines="0.2 0 0.8 1" begin="-1.5s" repeatCount="indefinite"/></circle><circle cx="50" cy="50" r="27.591" fill="none" stroke="#5699d2" stroke-width="2"><animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="3" keySplines="0 0.2 0.8 1" begin="0s" repeatCount="indefinite"/><animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="3" keySplines="0.2 0 0.8 1" begin="0s" repeatCount="indefinite"/></circle></svg>
		  </div>
		<form id="pay">
			<?= CSRF::csrfInputField() ?>
			<div id="dropin-container"></div>
			<button type="submit" id="submit-button" class="btn-main" disabled>Purchase</button>
			<input type="hidden" id="nonce" name="payment_method_nonce"></input>
			<input type="hidden" id="data" name="data"></input>
		</form>

</div>
  <script src="https://js.braintreegateway.com/web/3.90.0/js/client.min.js"></script>
  <script src="https://js.braintreegateway.com/web/3.90.0/js/data-collector.min.js"></script>
  <script src="https://js.braintreegateway.com/web/dropin/1.34.0/js/dropin.min.js"></script>
<script type="text/javascript" nonce="<?= $nonce ?>">
        var button = document.querySelector('#submit-button');
        var form = document.querySelector('#pay');
        var client_token = "<?php echo($gateway->ClientToken()->generate()); ?>";

        braintree.dropin.create({
          authorization: client_token,
          selector: '#dropin-container'
        }, function (createErr, instance) {

          if (createErr) {
            console.log('Create Error', createErr);
            return;
          }
		  
            document.querySelector('.preloader').style.opacity = 0;
            document.querySelector('.preloader').style.zIndex = -1;
          button.addEventListener('click', function (event) {
            event.preventDefault();
			// re-hide msg box so it doesnt seem as though it never goes away
			document.querySelector("#id").hidden = true;

            instance.requestPaymentMethod(function (err, payload) {
              if (err) {
                console.log('Request Payment Method Error', err);
                return;
              }
			  //window.location.href = window.location.origin + '/confirmation';
			  // Set the preloader background to transparent
              document.querySelector('.preloader').style.background = "transparent";
			  // Hide the "Choose another way to pay" button that appears at the weirdest time? Why they have this appear is completely beyond me?
			  document.querySelector(".braintree-large-button").style.display = "none";
			  // Disable the button, stop resubmission
			  button.disabled = true;
			  button.style.background = "grey";
			  button.innerText = "Paying...";
			  document.querySelector('.preloader').style.zIndex = 50;
			  document.querySelector('.preloader').style.opacity = 100;
			  
              // Add the nonce to the form and submit
              document.querySelector('#nonce').value = payload.nonce;
			  
			  const httpRequest = new XMLHttpRequest();
			  httpRequest.open("POST", window.location.href, true);
			  //httpRequest.setRequestHeader('Content-type', 'multipart/form-data');
			  var formData=new FormData(document.querySelector('#pay'));
			  httpRequest.send(formData);
			  httpRequest.onreadystatechange = function() {//Call a function when the state changes. 
				  if(httpRequest.readyState == 4 && httpRequest.status == 200) {
					  console.log(httpRequest.responseText);
					  const obj = JSON.parse(httpRequest.responseText);
					  console.log(httpRequest.responseText);
					  //console.log(obj.status);
					  if ( obj.status != 'success') {
						  console.log(obj.processorResponseText);
						  document.getElementById("message").innerHTML = obj.processorResponseText;
						  instance.clearSelectedPaymentMethod();
						  document.querySelector("#id").hidden = false;
						  document.querySelector('.preloader').style.zIndex = -1;
						  document.querySelector('.preloader').style.opacity = 0;
						  button.disabled = false;
						  button.style.background = "black";
						  button.innerText = "Try Again";
					  } else {
						  console.log(obj.processorResponseText);
						  window.location.href = window.location.origin + '/confirmation?id=' + obj.id;
					  }
			  	  }
			  }
			  
            });
          });

	  if (instance.isPaymentMethodRequestable()) {
		// This will be true if you generated the client token
		// with a customer ID and there is a saved payment method
		// available to tokenize with that customer.
		button.removeAttribute('disabled');
	  }

	  instance.on('paymentMethodRequestable', function (event) {
		//console.log(event.type); // The type of Payment Method, e.g 'CreditCard', 'PayPalAccount'.
		//console.log(event.paymentMethodIsSelected); // true if a customer has selected a payment method when paymentMethodRequestable fires

		button.removeAttribute('disabled');
	  });

	  instance.on('noPaymentMethodRequestable', function () {
		button.setAttribute('disabled', true);
	  });
});

braintree.client.create({
  authorization: '<?php echo($gateway->ClientToken()->generate()); ?>'
}, function (err, clientInstance) {
  // Creation of any other components...

  braintree.dataCollector.create({
    client: clientInstance
  }, function (err, dataCollectorInstance) {
    if (err) {
      console.log(err);
      return;
    }
	// correlation_id
	// console.log(JSON.parse(dataCollectorInstance.deviceData).correlation_id);
    // At this point, you should access the dataCollectorInstance.deviceData value and provide it
    // to your server, e.g. by injecting it into your form as a hidden input.
    document.getElementById("data").value = dataCollectorInstance.deviceData;
  });
});

</script>
</body>