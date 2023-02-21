<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/../csrf.php';
require __DIR__ . '/db.php';

$products;
$searchEmpty = true;
$page = 1;
$results_per_page = 10;
$page_first_result;
$number_of_pages;

$statement = $pdo->prepare("SELECT * FROM categories ORDER BY title");
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

if(!isset($_GET['p'])) {
	$page = 1;
} else {
	$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT);
}

if(isset($_POST['q']) && isset($_GET['c']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
	$query = filter_input(INPUT_POST, 'q');
	$category = filter_input(INPUT_GET, 'c');
	$statement = $pdo->prepare("SELECT * FROM products WHERE category='$category' AND CONCAT(`title`, `price`, `description`, `category`) LIKE '%$query%'");
	$statement->execute();
	if($statement->rowCount() > 0){
		$products = $statement->fetchAll(PDO::FETCH_ASSOC);
		$searchEmpty = false;
	}
} elseif(isset($_POST['q']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
	$query = filter_input(INPUT_POST, 'q');
	$statement = $pdo->prepare("SELECT * FROM products WHERE CONCAT(`title`, `price`, `description`, `category`) LIKE '%$query%'");
	$statement->execute();
	if($statement->rowCount() > 0){
		$products = $statement->fetchAll(PDO::FETCH_ASSOC);
		$searchEmpty = false;
	}
} elseif(isset($_GET['c'])) {
	$page_first_result = ($page - 1) * $results_per_page;
	$statement = $pdo->prepare("SELECT count(*) FROM products WHERE category=?");
	$statement->execute(array(filter_input(INPUT_GET, 'c')));
	$number_of_result = $statement->fetchColumn();
	$number_of_pages = ceil($number_of_result / $results_per_page);

	$statement = $pdo->prepare("SELECT * FROM products WHERE category=? LIMIT $page_first_result, $results_per_page");
	$statement->execute(array(filter_input(INPUT_GET, 'c')));
	if($statement->rowCount() > 0) {
		$products = $statement->fetchAll(PDO::FETCH_ASSOC);
		$searchEmpty = false;
	}
} else {
	$page_first_result = ($page - 1) * $results_per_page;
	$statement = $pdo->prepare("SELECT count(*) FROM products");
	$statement->execute();
	$number_of_result = $statement->fetchColumn();
	$number_of_pages = ceil($number_of_result / $results_per_page);
	$statement = $pdo->prepare("SELECT * FROM products LIMIT $page_first_result, $results_per_page");
	$statement->execute();
	if($statement->rowCount() > 0) {
		$products = $statement->fetchAll(PDO::FETCH_ASSOC);
		$searchEmpty = false;
	}
}



$csrf = CSRF::csrfInputField();
?>
<section class="products section">
	<div class="container">
		<div class="row">
			<div class="col-md-3">
				<div class="widget product-category">
					<h4 class="widget-title">Categories</h4>
					<div class="panel-group commonAccordion" id="accordion" role="tablist" aria-multiselectable="true">
					  	<div class="panel panel-default">
							<div id="collapseOne" class="" role="tabpanel" aria-labelledby="headingOne">
								<div class="panel-body">
									<ul>
										<li><a href="/products">All</a></li>
										<?php foreach($categories as $category): ?>
											<li><a href="/products?c=<?= $category['title']; ?>"><?= $category['title']; ?></a></li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
					  	</div>
						<br>
						<?php if(isset($_GET['c'])): ?>
							<form action="/products?c=<?= filter_input(INPUT_GET, 'c') ?>" method="post">
								<?= $csrf ?>
							    <div class="form-group">
								    <input name="q" type="search" class="form-control" placeholder="Search...">
						<?php else: ?>
							<form action="/products" method="post">
								<?= $csrf ?>
							    <div class="form-group">
								    <input name="q" type="search" class="form-control" placeholder="Search...">
						<?php endif ?>
							    </div>
							<div class="text-center p-3">
								<button name="search" type="submit" class="btn btn-main btn-small">Search</button>
							</div>
						</form>
					</div>
					
				</div>
			</div>
			<div class="col-md-9">
				<div class="row">
					<?php if($searchEmpty == false): ?>
						<?php foreach($products as $product): ?>
							<div class="col-md-4">
								<div class="product-item">
								  <a href="/item?id=<?= $product['id'] ?>">
									<div class="product-thumb">
										<!--<span class="bage">Sale</span>-->
										<img class="img-responsive" src="<?= (!empty(unserialize($product['images'])[0]))?unserialize($product['images'])[0]:'/images/noimg.jpg'; ?>" alt="product-img" />
									</div>
								  </a>
									<div class="product-content">
										<h4><a href="/item?id=<?= $product['id'] ?>"><?= $product['title'] ?></a></h4>
										<p class="price">$ <?= number_format($product['price'], 2) ?></p>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div>
							<div class="block text-center">
								<i class="tf-ion-ios-cart-outline"></i>
								<h2 class="text-center">No items found.</h2>
								<a href="/products" class="btn btn-main mt-20">Return to shop</a>
							</div>
						</div>
					<?php endif ?>

		

				</div>				
			</div>
		
		</div>
		<?php if(!isset($_POST['q'])): ?>
			<div class="row">
				<div class="col-sm-12 text-center">
					<?php
						if(isset($_GET['c'])){
							if($page == 1) {
								for($i = $page; $i <= $number_of_pages; $i++) {
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $i . '">' . $i . '</a>';
									if($i == 3) {
										break;
									}
								}
							} elseif($page == $number_of_pages) {
								if($page - 3 > 0) {
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page - 2 . '">' . $page - 2 . ' </a>';
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page - 1 . '">  ' . $page - 1 . ' </a>';
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page  . '">  ' . $page . '</a>';
								} elseif($page - 2 > 0) {
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page - 1 . '">  ' . $page - 1 . ' </a>';
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page . '">  ' . $page . ' </a>';
								} else {
									echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page . '">  ' . $page . ' </a>';
								}
							} else {
								echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page - 1 . '">  ' . $page - 1 . ' </a>';
								echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page . '">  ' . $page . ' </a>';
								echo '<a href="/products?c=' . filter_input(INPUT_GET, 'c') . '&p=' . $page + 1 . '">  ' . $page + 1 . ' </a>';
							}
						} else {
							if($page == 1) {
								for($i = $page; $i <= $number_of_pages; $i++) {
									echo '<a href="/products?p=' . $i . '">' . $i . '</a>';
									if($i == 3) {
										break;
									}
								}
							} elseif($page == $number_of_pages) {
								if($page - 3 > 0) {
									echo '<a href="/products?p=' . $page - 2 . '">  ' . $page - 2 . ' </a>';
									echo '<a href="/products?p=' . $page - 1 . '">  ' . $page - 1 . ' </a>';
									echo '<a href="/products?p=' . $page  . '">  ' . $page . '</a>';
								} elseif($page - 2 > 0) {
									echo '<a href="/products?p=' . $page - 1 . '">  ' . $page - 1 . ' </a>';
									echo '<a href="/products?p=' . $page . '">  ' . $page . ' </a>';
								} else {
									echo '<a href="/products?p=' . $page . '">  ' . $page . ' </a>';
								}
							} else {
								echo '<a href="/products?p=' . $page - 1 . '">  ' . $page - 1 . ' </a>';
								echo '<a href="/products?p=' . $page . '">' . $page . ' </a>';
								echo '<a href="/products?p=' . $page + 1 . '">  ' . $page + 1 . ' </a>';
							}   
						}
					?>
				</div>
			</div>
		<?php endif ?>
	</div>
</section>
<?php require __DIR__ . '/footer.php'; ?>