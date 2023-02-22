<?php

require __DIR__ . '/header.php'; 
require __DIR__ . '/../csrf.php';
require __DIR__ . '/abuseipdb.php';
$error = false;
$errormsg = array();
if(isset($_POST['checkout']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
  if(!isset($_SESSION['name'])) {
    header('Location: /login');
  } else {
	if(AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)){$error = true && array_push($errormsg, 'Code 69');}
	foreach($_SESSION['cart'] as $item) {
		if(cartcheck($item['id'], $item['quantity']) == -1) {
			$error = true;
			array_push($errormsg, '"' . $item['title'] . '" is out of stock!');
		}
	}
	if (!$_POST['selectedshipping']) {$error = true && array_push($errormsg, 'Shipping option error') ; }
	if (!$_POST['method']) {$error = true && array_push($errormsg, 'error') ; }
	if(!$error) {
	$_SESSION['shipping'] = filter_input(INPUT_POST, 'selectedshipping');
	$_SESSION['method'] = filter_input(INPUT_POST, 'method');
    header('Location: /checkout');
	}
  }
}

$nonce = bin2hex(openssl_random_pseudo_bytes(64));
header("Content-Security-Policy: default-src 'none';connect-src 'self';script-src-elem 'self' 'nonce-" . $nonce . "' https://code.jquery.com/jquery-3.6.3.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js;style-src-elem 'self' https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.compat.css https://assets.braintreegateway.com/web/dropin/1.34.0/css/dropin.min.css https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css https://fonts.googleapis.com/;style-src 'self';font-src 'self' https://fonts.gstatic.com/;img-src 'self' data:; report-uri /csp/report;");
if(empty($config['tax'])){
	$tax = 0;
}else{
	$tax = $config['tax'];
}
$csrf = CSRF::csrfInputField();
?>
<?php if(!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0): ?>
<section class="empty-cart page-wrapper">
  <div class="container">
    <div class="row">
      <div class="">
        <div class="block text-center">
        	<i class="tf-ion-ios-cart-outline"></i>
          	<h2 class="text-center">Your cart is currently empty.</h2>
          	<a href="/products" class="btn btn-main mt-20">Return to shop</a>
      </div>
    </div>
  </div>
</section>
<?php else: ?>
<div class="page-wrapper">
  <div class="cart shopping">
    <div class="container">
	<?php if($error): ?>
            <div class="text-center">
                <div>
                    <div class="alertPart">
                    <div class="alert alert-danger errreg" role="alert"><i class="tf-ion-close-circled"></i><span>Checkout Failed! </span>The following errors have occurred! <ul><?php foreach($errormsg as $msg){echo '<li>'.$msg.'<li>';} ?></ul></div>
                    </div>
                </div>		
            </div>
	<?php endif ?>
      <div class="row">
        <div>
          <div class="block">
            <div class="product-list">
                <table class="table">
                  <thead>
                    <tr>
                      <th class="">Item Name</th>
                      <th class="">Qty</th>
                      <th class="">Actions</th>
                      <th class="">Sub Total</th>
                      <th class="">Total</th>
                    </tr>
                  </thead>
                  <tbody>

                      <?php foreach($_SESSION['cart'] as $item):
					(cartcheck($item['id'], $item['quantity']) >= 0)?$stock = 'text-success':$stock = 'text-danger'; ?>
                        <tr class="<?= $stock ?>">
                          <td class="">
                            <div class="product-info">
                              <img width="80" src="<?= $item['image'] ?>" alt="" />
                              <a class="font-12" href="/item?id=<?= $item['id'] ?>"><?= $item['title'] ?></a>
                            </div>
                          </td>
                          <td class="">   <?= $item['quantity'] ?></td>
                          <td class="">
                            <a href="/cart-remove-item?id=<?= $item['id'] ?>" class="product-remove">Remove</a>
                          </td>
                          <td class=""><?= $config['currency_symbol'].number_format($item['price'] * $item['quantity'], 2) ?></td>
                          <td class=""><?php
                            $itemtotal = 0;
                            foreach($_SESSION['cart'] as $item) {
                              $itemtotal += $item['price'] * $item['quantity'];
                            }
							$taxed = 0;
							foreach(explode(',', $tax) as $rate) {
								$taxed += tax($itemtotal, $rate);
							}
                            echo $config['currency_symbol'].number_format(bcadd($itemtotal, $taxed, 2), 2);
						  ?></td>
                        </tr>
                      <?php endforeach; ?>

                    <tr class="">
                      <td class="">
                        <div class="product-info">
                          <a href="#!">Total</a>
                        </div>
                      </td>
                      <td class=""></td>
                      <td class=""></td>
                      <td class=""><?php
                            $notaxtotal = 0;
                            foreach($_SESSION['cart'] as $item) {
                              $notaxtotal += $item['price'] * $item['quantity'];
                            }
                            echo $config['currency_symbol'].number_format($notaxtotal, 2);
                        ?>
                      </td>
					  <td id="total"><?php
                            $total = 0;
                            $weight = 0; // add weight here, we'll use it later in the script
                            foreach($_SESSION['cart'] as $item) {
                              $total += $item['price'] * $item['quantity'];
                              $weight += $item['weight'] * $item['quantity'];
                            }
							$taxed = 0;
							foreach(explode(',', $tax) as $rate) {
								$taxed += tax($total, $rate);
							}
                            echo $config['currency_symbol'].number_format(bcadd($total, $taxed, 2), 2);
                        ?></td>
                    </tr>
                  </tbody>
                </table>
                <form action="/cart" method="post">
                  <?= $csrf ?>
				  <input name="selectedshipping" id="selectedshipping" type="hidden"></input>
				  <input name="method" id="method" type="hidden"></input>
                  <button id="checkout" name="checkout" type="submit" class="btn btn-main float-end" disabled>Checkout</button>
                </form>
				  <div class="shipping-selector">
				  <div id="msgbox" class="alert alert-danger text-center p-1" hidden="true">Invalid postal code</div>
				  <ul>
					<form id="ship">
					  <li><input name="weight" id="weight" type="hidden" value="<?= $weight ?>"></input></li>
					  <li><input name="hiddentotal" id="hiddentotal" type="hidden" value="<?= number_format(bcadd($total, $taxed, 2), 2) ?>"></input></li>
					  <li class="p-1"><select id="selector" class="selector font-12" name="selector" required>
					  </select></li>
					  <li class="p-1"><input id="postal" min=3 class="postal" name="postal" placeholder="A1B2D3" required></input></li>
					  <li class="p-1"><button type="button" class="btn-small" id="btn" name="shipping_btn" disabled>Get shipping quote</button></li>
				  </form>
					<p class="font-10">* Canadian only, please contact us for international shipments</p>
				  </ul>
				 </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript" nonce="<?= $nonce ?>">
var btn = document.querySelector("#btn");
var form = document.querySelector("#ship");
var input = document.querySelector("#postal");
input.addEventListener("input", stateHandle);
function stateHandle() {
 if (input.value !="") {
  btn.disabled = false;
 }
 else {
  btn.disabled = true;
 }
}
// Remove options function, re-add default option
function removeOptions(selectElement) {
   var i, L = selectElement.options.length - 1;
   for(i = L; i >= 0; i--) {
      selectElement.remove(i);
   }
		let defaultOption = document.createElement('option');
		defaultOption.text = 'Choose Shipping';
		defaultOption.value = 'default';
		dropdown.add(defaultOption);
}
					  
let dropdown = document.querySelector('#selector');
dropdown.length = 0;
// Create default option
let defaultOption = document.createElement('option');
defaultOption.text = 'Choose Shipping';
defaultOption.value = 'default';
dropdown.add(defaultOption);
dropdown.selectedIndex = 0;

	btn.addEventListener('click', function (event) {
		console.log('click');
		// Prevent double submit!
		btn.disabled = true;
		btn.style.background = 'grey';
		// Remove options so we dont add to the list
		removeOptions(dropdown);
			  // Make POST request
			  const httpRequest = new XMLHttpRequest();
			  httpRequest.open("POST", window.location.origin + '/shipping-calculator', true);
			  var formData=new FormData(form);
			  httpRequest.send(formData);
			  httpRequest.onreadystatechange = function() {//Call a function when the state changes. 
				  if(httpRequest.readyState == 4 && httpRequest.status == 200) {
					  console.log(httpRequest.responseText);
						// Add dropdown options from JSON response
						const data = JSON.parse(httpRequest.responseText);
					    if (data[0].success) {
							document.getElementById("msgbox").hidden = true;
							document.getElementById("btn").style.background = 'green';
							let option;
							for (let i = 0; i < data.length; i++) {
							  option = document.createElement('option');
							  option.text = data[i].Service + ' ' + data[i].Price;
							  option.value = data[i].Price;
							  option.id = data[i].Service;
							  dropdown.add(option);
							}
						} else {
							btn.disabled = false;
							if ( data[0].Code == "9111" ) { document.getElementById("msgbox").innerHTML = 'Shippment too large, please contact us'; }
							if ( data[0].Code == "503" ) { document.getElementById("msgbox").innerHTML = 'Canada Post Api Error'; }
							document.getElementById("msgbox").hidden = false;
							console.log(data[0].Code);
							document.getElementById("btn").style.background = 'black';
							document.getElementById("btn").style.color = 'white';
						}
					// Re-enable the button in case user changes postal code
				  }
			 }
	});
	// 
	dropdown.addEventListener('change', (event) => {
		if (dropdown.value != 'default') {
			//enable button if not default option
			document.querySelector("#checkout").disabled = false;
			// many suggest to use ajax to get the id of the option selected, this is a ridiculous suggestion.
			document.querySelector("#method").value = $("#selector option:selected").attr("id");
			document.querySelector("#selectedshipping").value = dropdown.value;
			document.getElementById("total").innerHTML = document.getElementById("hiddentotal").value;
			// strip the comma and add values
			let total = parseFloat(Number(document.getElementById("total").innerHTML.replace(/,/g, '')) + Number(dropdown.value)).toFixed(2);
			// insert + add the comma back
			document.getElementById("total").innerHTML = '$' + total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			//console.log(document.getElementById("total").innerHTML);
			
		} else {
			// disable button, is default selected
			document.querySelector("#checkout").disabled = true;
			// reset cost to original
			document.getElementById("total").innerHTML = '$' + document.getElementById("hiddentotal").value;
		}
	});

</script>
<?php endif ?>
<?php require __DIR__ . '/footer.php'; ?>
