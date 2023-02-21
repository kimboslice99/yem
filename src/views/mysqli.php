<?php

$config = parse_ini_file(__DIR__ . '/bin/config.ini');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
	$mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name'], $config['db_port']);
	$mysqli->set_charset("utf8mb4");
} catch( mysqli_sql_exception $exception ) {
	trigger_error("Connection error :" . $exception);
	?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Site Down!</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Site Down!">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.png" />
  <link rel="stylesheet" href="/plugins/themefisher-font/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <link rel="stylesheet" href="/plugins/animate/animate.css">
  <link rel="stylesheet" href="/plugins/slick/slick.css">
  <link rel="stylesheet" href="/plugins/slick/slick-theme.css">
  <link rel="stylesheet" href="/css/style.css">

</head>

<body id="body">

	<section class="page-404">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1>503</h1>
					<h2>Backend Errors, Sorry</h2>
					<a href="/" class="btn btn-main"><i class="tf-ion-android-arrow-back"></i> Go Home</a>
					<p class="copyright-text">Copyright &copy;<?= date('Y') ?></p>
				</div>
			</div>
		</div>
	</section>

	 <!-- 
    Essential Scripts
    =====================================-->
    
    <!-- Main jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- Popper 2.11.6 -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- Bootstrap 5.3.0 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <!-- Bootstrap Touchpin -->
    <script src="/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Count Down Js -->
    <script src="/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel -->
    <script src="/plugins/slick/slick.min.js"></script>
    <script src="/plugins/slick/slick-animation.min.js"></script>

    <!-- Main Js File -->
    <script src="/js/script.js"></script>
    
    

  </body>
  </html><?php
}
?>