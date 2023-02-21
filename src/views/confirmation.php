<?php 
ob_start();

require __DIR__ . '/header.php';
require __DIR__ . '/invoice.php';

if (isset($_GET["id"])) {
	// Prevent refreshing confirmation page causing resubmission of order
	if(!empty($_SESSION['cart'])) {
		
	}
}


?>
<!-- Page Wrapper -->
<section class="page-wrapper success-msg">
  <div class="container">
    <div class="row">
      <div>
        <div class="block text-center">
        	<i class="tf-ion-android-checkmark-circle"></i>
          <h2 class="text-center">Thank you for shopping with us!<br>Your order ID is <?= $_GET['id'] ?></h2>
          <a href="/products" class="btn btn-main mt-20">Continue Shopping</a>
        </div>
      </div>
    </div>
  </div>
</section><!-- /.page-warpper -->

<?php require __DIR__ . '/footer.php'; ?>