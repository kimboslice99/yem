<?php

if(!isset($_GET['id'])) {
    header('Location: /404');
}

$inCart = false;
require __DIR__ . '/../csrf.php';


if(isset($_POST['cart']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
	$_SESSION['cart'][$_POST['id']] = array(
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'price' => $_POST['price'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'quantity' => $_POST['quantity'],
        'image' => $_POST['image'],
        'weight' => $_POST['weight']
    );
}
if(!empty($_SESSION['cart'])){
	foreach($_SESSION['cart'] as $item) {
		if($item['id'] == $_GET['id']) {
			$inCart = true;
			break;
		}
	}
}

require __DIR__ . '/header.php';
require __DIR__ . '/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$statement = $pdo->prepare("SELECT * FROM products WHERE id=?");
$statement->execute(array($id));
if($statement->rowCount() > 0) {
$item = $statement->fetchAll(PDO::FETCH_ASSOC);
$images = unserialize($item[0]['images']);
($item[0]['qty'] <= 0)?$stock = false:$stock = true;
$statement = $pdo->prepare("SELECT * FROM products WHERE category=? ORDER BY rand() LIMIT 4");
$statement->execute(array($item[0]['category']));
$relatedItems = $statement->fetchAll(PDO::FETCH_ASSOC);
}
//var_dump($_SESSION['cart']);
header("Content-Security-Policy: default-src 'none';connect-src 'self';script-src-elem 'self' https://code.jquery.com/jquery-3.6.3.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js;style-src-elem 'self' https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.compat.css https://assets.braintreegateway.com/web/dropin/1.34.0/css/dropin.min.css https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css https://fonts.googleapis.com/;style-src 'self' 'unsafe-inline';font-src 'self' https://fonts.gstatic.com/;img-src 'self' data:; report-uri /csp/report;");
?>
<?php if(!empty($item)): ?>
<section class="single-product">
    <div class="container">
        <div class="row">
			<div class="col-md-6">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="/">Home</a></li>
					<li class="breadcrumb-item"><a href="/products">Shop</a></li>
					<li class="breadcrumb-item active"><?= $item[0]['category']; ?></li>
				</ol>
			</div>
		</div>
        <div class="row mt-20">
            <div class="col-md-7">
                <div class="single-product-slider w-75">
					<div id="carousel" class="carousel slide" data-bs-ride="carousel">
                        <div class='carousel-outer'>
                            <!-- me art lab slider -->
                            <div class="carousel-inner">
                                <?php if(count($images) > 1): ?>
									  <div class="carousel-item active">
									  	<a href="<?= $images[0] ?>" data-toggle="lightbox" data-gallery="example-gallery">
										<img class="img-fluid d-block w-100" src="<?= $images[0] ?>" >
										</a>
									  </div>
                                    <?php 
                                        foreach($images as $key=>$image){
                                            if($key == 0) {
                                                continue;
                                            }
                                          echo "<div class='carousel-item'>";
										  echo '<a href="' . $image . '" data-toggle="lightbox" data-gallery="example-gallery">';
                                          echo "<img src='" . $image . "' class='img-fluid d-block w-100'></a>";
                                          echo "</div>";
                                        }
                                    ?>
                                <?php elseif(count($images) == 1): ?>
                                    <div class='carousel-item active'>
									  <a href="<?= $images[0] ?>" data-toggle="lightbox" data-gallery="example-gallery">
                                        <img src='<?= $images[0] ?>' alt='Product Image' class="img-fluid d-block w-100" >
									  </a>
                                    </div>  
                                <?php else: ?>
									<div class="carousel-item active">
										<img src="/images/noimg.jpg" alt="Product Image" class="img-fluid d-block w-100">
									</div>
                                <?php endif ?>

                                
                            </div>
							<a class="carousel-control-prev" href="#carousel" role="button" data-bs-slide="prev">
							  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
							  <span class="sr-only">Previous</span>
							</a>
							
							<a class="carousel-control-next" href="#carousel" role="button" data-bs-slide="next">
							  <span class="carousel-control-next-icon" aria-hidden="true"></span>
							  <span class="sr-only">Next</span>
							</a>
                        </div>
                        <!-- thumb -->
                        <ol class='carousel-indicators'>
                            <?php foreach($images as $key => $image): ?>
                                <li data-bs-target='#carousel' data-bs-slide-to='<?= $key ?>' class='active'>
                                    <img src='<?= $image ?>' alt='' />
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <form action="/item?id=<?= $item[0]['id'] ?>" method="post">
                    <div class="single-product-details">
                        <h2><?= $item[0]['title'] ?></h2>
                        <?= CSRF::csrfInputField() ?>
                        <input type="text" name="title" value="<?= $item[0]['title'] ?>" hidden>
                        <p class="product-price">CAD$<?= number_format($item[0]['price'], 2) ?></p>
                        <input type="text" name="price" value="<?= $item[0]['price'] ?>" hidden>
                        <input type="text" name="weight" value="<?= $item[0]['weight'] ?>" hidden>
                        <input type="text" name="image" value="<?= (!empty(unserialize($item[0]['images'])[0]))?unserialize($item[0]['images'])[0]:'/images/noimg.jpg'; ?>" hidden>
                        <p class="product-description mt-20">
                            <?= $item[0]['description'] ?>
                            <input type="text" name="description" value="<?= $item[0]['description'] ?>" hidden>
                        </p>
                        <div class="product-quantity">
                            <span>Quantity:</span>
                            <div class="product-quantity-slider">
                                <input id="product-quantity" type="number" min=0 max=<?= $item[0]['qty'] ?> value="1" name="quantity">
                            </div>
                        </div>
                        <div class="product-category">
                            <span>Categories:</span>
                            <ul>
                                <li><a href="/products?c=<?= $item[0]['category'] ?>"><?= $item[0]['category'] ?></a></li>
                                <input type="text" name="category" value="<?= $item[0]['category'] ?>" hidden>
                            </ul>
                        </div>
						<input type="text" name="id" value="<?= $item[0]['id'] ?>" hidden>
                        <?php if($inCart): ?>
							<button name="cart" type="submit" class="btn btn-main text-center text-success" disabled>In Cart!</button>
						<?php elseif(!$stock): ?>
							<button name="cart" type="sumit" class="btn btn-main text-center text-danger" disabled>Out of Stock!</button>
						<?php else: ?>
							<button name="cart" type="submit" class="btn btn-main text-center">Add to Cart</button>
						<?php endif ?>
                    </div>
                </form>
            </div>
         </div>
    </div>
</section>
<section class="products related-products section">
	<div class="container">
		<div class="row">
			<div class="title text-center">
				<h2>Related Products</h2>
			</div>
		</div>
		<div class="row">
			<?php foreach($relatedItems as $item): ?>
    			<div class="col-md-3">
    				<div class="product-item">
    				<a href="/item?id=<?= $item['id'] ?>">
    					<div class="product-thumb">
    						<img class="img-fluid" src="<?= (!empty(unserialize($item['images'])[0]))?unserialize($item['images'])[0]:'/images/noimg.jpg'; ?>" alt="product-img">
    						<div class="preview-meta">
    							<ul>
    								<li>
    									<span  data-toggle="modal" data-target="#product-modal">
    										<i class="tf-ion-ios-search"></i>
    									</span>
    								</li>
    								<li>
    			                        <a href="#" ><i class="tf-ion-ios-heart"></i></a>
    								</li>
    								<li>
    									<a href="#!"><i class="tf-ion-android-cart"></i></a>
    								</li>
    							</ul>
                          	</div>
    					</div>
    					<div class="product-content">
    						<h4><a href="/item?id=<?= $item['id'] ?>"><?= $item['title'] ?></a></h4>
    						<p class="price">$ <?= number_format($item['price'], 2) ?></p>
    					</div>
					</a>
    				</div>
    			</div>
			<?php endforeach; ?>

		</div>
	</div>
</section>
<?php
require __DIR__ . '/footer.php';
  else: 
	require __DIR__ . '/../error.php' ;
endif
?>