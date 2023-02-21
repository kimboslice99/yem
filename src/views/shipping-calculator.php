<?php
// Lets return JSON and parse it client side for a shipping calculator that doesnt require the traditional form submit
if(isset($_POST['postal'])) {
$config = parse_ini_file(__DIR__ . '/bin/config.ini');
if(empty($config['cp_username']) || empty($config['cp_password']) || empty($config['cp_mailedby'])){die();}
$username = $config['cp_username']; 
$password = $config['cp_password'];
$mailedBy = $config['cp_mailedby'];

// REST URL
$service_url = 'https://ct.soa-gw.canadapost.ca/rs/ship/price';

// Create GetRates request xml
$originPostalCode = $config['cp_postal'];
// Strip spaces
$postalCode = preg_replace('/\s/i', '', strtoupper(filter_input(INPUT_POST, 'postal')));
$weight = filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_INT);

$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v4">
  <customer-number>{$mailedBy}</customer-number>
  <parcel-characteristics>
    <weight>{$weight}</weight>
  </parcel-characteristics>
  <origin-postal-code>{$originPostalCode}</origin-postal-code>
  <destination>
    <domestic>
      <postal-code>{$postalCode}</postal-code>
    </domestic>
  </destination>
</mailing-scenario>
XML;

$curl = curl_init($service_url); // Create REST Request
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . $password);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.cpc.ship.rate-v4+xml', 'Accept: application/vnd.cpc.ship.rate-v4+xml'));
$curl_response = curl_exec($curl); // Execute REST Request
if(curl_errno($curl)){
	//echo 'Curl error: ' . curl_error($curl) . "\n";
	die(json_encode(array('success' => false, 'message' => curl_error($curl))));
}

curl_close($curl);
$json = array();
// Example of using SimpleXML to parse xml response
libxml_use_internal_errors(true);
$xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/','',$curl_response) . '</root>');
if ($xml) {
	if ($xml->{'price-quotes'} ) {
		$priceQuotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v4');
		if ( $priceQuotes->{'price-quote'} ) {
			foreach ( $priceQuotes as $priceQuote ) {  
				$Service = $priceQuote->{'service-name'};
				$Price = $priceQuote->{'price-details'}->{'due'};
				array_push($json, array('success' => true, 'Service' => (string)$Service, 'Price' => (string)$Price));
			}
			echo json_encode($json);
		}
	}
	if ($xml->{'messages'} ) {
		$messages = $xml->{'messages'}->children('http://www.canadapost.ca/ws/messages');		
		foreach ( $messages as $message ) {
			$code = $message->code;
			$error = $message->description;
			if ( $code == "9111" ) { $error = $weight . ' Shipment too large/heavy, please contact us for a quote'; }
			if(!$error){$message='Unknown error occured';}
			array_push($json, array('success' => false, 'Code' => (string)$code, 'message' => $error));
		}
		echo json_encode($json);
	}
		
}
}

?>