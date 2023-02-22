<?php
ob_start();
$config = parse_ini_file(__DIR__ . '/bin/config.ini');
require __DIR__ . '/admin/util.php';

header("Content-Security-Policy: default-src 'none';connect-src 'self';script-src-elem 'self' https://code.jquery.com/jquery-3.6.3.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js;style-src-elem 'self' https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.compat.css https://assets.braintreegateway.com/web/dropin/1.34.0/css/dropin.min.css https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css https://fonts.googleapis.com/;style-src 'self';font-src 'self' https://fonts.gstatic.com/;img-src 'self' data:; report-uri /csp/report;");
$phone = $config['contact_phone'];
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <title><?= $config['title'] ?></title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="<?= $config['description'] ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  
  <!-- Sharing needs -->
  <meta property="og:title" content="<?= $config['title'] ?>">
  <meta property="og:description" content="<?= $config['description'] ?>">
  <meta property="og:image" content="<?= $config['meta_image'] ?>">
  <meta property="og:url" content="<?= preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST']) ?>">
  
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.png" />

  <link rel="stylesheet" href="/plugins/themefisher-font/style.css">
  <!-- bootstrap.min css -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <!-- Animate css -->
  <!--<link rel="stylesheet" href="/plugins/animate/animate.css">-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.compat.css">
  <!-- Slick Carousel
  <link rel="stylesheet" href="/plugins/slick/slick.css">
  <link rel="stylesheet" href="/plugins/slick/slick-theme.css"> -->
  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="/css/style.css">

</head>

<body id="body">

    <!-- Start Top Header Bar -->
    <section class="top-header">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-xs-12 col-sm-4">
				<?php if(!empty($phone)): ?>
                    <div class="contact-number">
                        <i class="tf-ion-ios-telephone"></i>
                        <span><?= $phone ?></span>
                    </div>
				<?php endif ?>
                </div>
                <div class="col-md-4 col-xs-12 col-sm-4">
                    <!-- Site Logo -->
                    <div class="logo text-center">
                        <a href="/">
							<img class="logo-h" src="/images/logo.jpg" alt="Logo">
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-xs-12 col-sm-4">
                    <!-- Cart -->
                    <ul class="top-menu text-right list-inline">
                        <li class="dropdown cart-nav cartdrop dropdown-slide">
                            <a href="#!" class="nav-link dropdown-toggler" data-bs-toggle="dropdown" data-bs-hover="dropdown"><i
                                    class="tf-ion-android-cart"></i>Cart</a>
                            <div class="dropdown-menu p-2 dropdown-menu-end">
                                
                                <?php if(!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0): ?>
                                    <div class="dropdown-item">
                                        <div class="media-body">
                                            <h4 class="media-heading text-center">Cart is empty</h4>
                                        </div>
                                    </div>

                                    <div class="cart-summary">
                                        <span>Total</span>
                                        <span class="total-price"><?= $config['currency_symbol'] ?> 0.00</span>
                                    </div>
                                    <ul class="text-center">
                                        <li><a href="/cart" class="btn btn-small">View Cart</a></li>
                                    </ul>
    
                                <?php else: ?>
								<table class="font-10 text-center">
									<tr>
									<!--<th>img</th>-->
									<th>title</th>
									<th>price</th>
									<th>qty</th>
									<th>total</th>
									<th>delete</th>
									</tr>
                                    <?php foreach($_SESSION['cart'] as $item): ?>
									<tr>
                                        <div class="dropdown-item">
                                            <a class="pull-left" href="#!">
                                                <!--<td><img class="media-object" src="\<\?= $item['image'] ?>" alt="image" /></td>-->
                                            </a>
                                            <div class="media-body">
                                                <td class="p-1"><p class="font-10"><a href=""><?= $item['title'] ?></a></p></td>
                                                <div class="cart-price">
                                                    <td class="p-1"><?= number_format($item['price'], 2) ?></td>
                                                    <td class="p-1"><?= $item['quantity'] ?></td>
                                                </div>
                                                <td class="p-1"><?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                                            </div>
                                            <td class="p-0"><a href="/cart-remove-item?id=<?= $item['id'] ?>"><i class="tf-ion-close"></i></a></td>
                                        </div>
									</tr>
                                    <?php endforeach; ?>
								</table>
                                    <div class="cart-summary">
                                        <span>Total</span>
                                        <span class="total-price"><?php 
                                                $total = 0;
                                                foreach($_SESSION['cart'] as $item) {
                                                    $total += $item['price'] * $item['quantity'];
                                                }
                                                echo $config['currency_symbol'].number_format($total, 2);
                                            ?>
                                        </span>
                                    </div>
                                    <ul class="text-center">
                                        <li><a href="/cart" class="btn btn-small" data-link>View Cart</a></li>
                                    </ul>
                                <?php endif ?>
                            </div>

                        </li>

                    </ul><!-- / .nav .navbar-nav .navbar-right -->
                </div>
            </div>
        </div>
    </section><!-- End Top Header Bar -->


    <!-- Main Menu Section -->
    <section class="menu">
		<nav class="navbar navbar-expand-lg navbar-light">
		  <div>

				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
					<span class="navbar-toggler-icon"></span>
				  </button>

                </div><!-- / .navbar-header -->

                <!-- Navbar Links d-flex justify-content-center -->
                <div class="collapse navbar-collapse text-center justify-content-center bg-light" id="navbarSupportedContent">
                    <ul class="navbar-nav d-flex">

                        <!-- Home -->
                        <li class="dropdown p-3">
                            <a href="/" data-link>Home</a>
                        </li><!-- / Home -->


                        <!-- Shop -->
                        <li class="dropdown p-3">
                            <a href="/products" data-link>Shop</a>
                        </li><!-- / Shop -->

                        <li class="dropdown p-3">
                            <a href="/about" data-link>About</a>
                        </li><!-- / About -->
                        <?php if(isset($_SESSION['name'])): ?>
						<div class="btn-group">
                            <li class="nav-item dropdown p-3 prof dropdown-slide">
                                <a class="dropdown-toggler" data-bs-display="static" href="#" role="button" data-bs-hover="dropdown" aria-expanded="false"><?= $_SESSION['name']; ?></a>
                                <ul class="dropdown-menu dropdown-menu-lg-end">
                                    <li><a class="dropdown-item" href="/profile">Profile</a></li>
                                    <li><a class="dropdown-item" href="/logout">Logout</a></li>
                                </ul>
                            </li>
						</div>
                        <?php else: ?>
						<div class="btn-group">
                            <li class="nav-item dropdown p-3 prof dropdown-slide">
                                <a class="dropdown-toggler" data-bs-display="static" href="#" role="button" data-bs-hover="dropdown" aria-expanded="false">Account</a>
                                <ul class="dropdown-menu dropdown-menu-lg-end">
                                    <li><a class="dropdown-item" href="/login">Login</a></li>
                                    <li><a class="dropdown-item" href="/register">Register</a></li>
                                </ul>
                            </li>
						</div>
                        <?php endif ?>

                    </ul><!-- / .nav .navbar-nav -->

                </div>
                <!--/.navbar-collapse -->
            </div><!-- / .container -->
        </nav>
    </section>

