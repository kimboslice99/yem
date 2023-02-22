<?php 

ob_start();

if(!isset($_SESSION['admin'])) {
    header('Location: /admin/login');
}
$config = parse_ini_file(__DIR__ . '/../bin/config.ini');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $config['title'] ?> | Dashboard</title>
    <link href="/a/vendor/fontawesome/css/fontawesome.min.css" rel="stylesheet">
    <link href="/a/vendor/fontawesome/css/solid.min.css" rel="stylesheet">
    <link href="/a/vendor/fontawesome/css/brands.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="/a/css/master.css" rel="stylesheet">
    <link href="/a/vendor/datatables/datatables.min.css" rel="stylesheet">
    <link href="/a/vendor/flagiconcss/css/flag-icon.min.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <nav id="sidebar" class="active">
            <ul class="list-unstyled components text-secondary">
                <li>
                    <a href="/admin/home"><i class="fas fa-home"></i>Home</a>
                </li>
                <li>
                    <a href="/admin/products"><i class="fas fa-shopping-cart"></i>Products</a>
                </li>
                <li>
                    <a href="/admin/customers"><i class="fas fa-user"></i></i>Customers</a>
                </li>
                <li>
                    <a href="/admin/orders"><i class="fas fa-file"></i>Orders</a>
                </li>
                <li>
                    <a href="/admin/faq"><i class="fas fa-info-circle"></i>Faq</a>
                </li>
                <li>
                    <a href="/admin/settings"><i class="fas fa-cog"></i>Settings</a>
                </li>
            </ul>
        </nav>
        <div id="body" class="active">
            <!-- navbar navigation component -->
            <nav class="navbar navbar-expand-lg navbar-white bg-white">
                <button type="button" id="sidebarCollapse" class="btn btn-light">
                    <i class="fas fa-bars"></i><span></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ms-auto">
                     
                        <li class="nav-item dropdown">
                            <div class="nav-dropdown">
                                <a href="#" id="nav2" class="nav-item nav-link dropdown-toggle text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> <span><?= $_SESSION['admin'] ?></span> <i class="fas font-10 fa-caret-down"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end nav-link-menu">
                                    <ul class="nav-list">
                                        <li><a href="/admin/reset-password" class="dropdown-item"><i class="fas fa-address-card"></i> Reset Password</a></li>
                                        <li><a href="/admin/logout" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- end of navbar navigation -->
            <div class="content">