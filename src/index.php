<?php
// ============== ERROR REPORTING ================ //
error_reporting(E_ALL & ~E_NOTICE); //  quiet down log with & ~E_NOTICE
ini_set('display_errors', 'Off');
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/config/php-error.log");
// ================== CHECK INI ===================== //
if(!file_exists(__DIR__ . '/config/config.ini')){
	die('Please rename config.ini.sample to config.ini and fill in your config details');
} else {
	$config = parse_ini_file(__DIR__ . '/config/config.ini');
}
// ============= CHECK IF CONFIG DIR WRITEABLE ============= //
if(!$f = @fopen(__DIR__.'/config/writeable.tmp', 'w')){
	die('config directory not writable!');
} else {
	fclose($f);
	unlink(__DIR__.'/config/writeable.tmp');
}
// ============ CHECK FOR REQUIRED EXTENSIONS ========== //
(!extension_loaded('curl'))?die('curl missing!'):''; // Shipping calc
(!extension_loaded('imagick'))?die('imagick missing!'):''; // image manipulation
(!extension_loaded('openssl'))?die('openssl missing!'):''; // csrf tokens
(!extension_loaded('zlib'))?die('zlib missing!'):''; // gzopen() gzwrite() gzclose()
(!extension_loaded('session'))?die('session missing!'):''; // duh
(!extension_loaded('mysqli'))?die('mysqli missing!'):''; // aLL db operations
(!extension_loaded('bcmath'))?die('bcmath missing!'):''; // math calculations fo taxes
(!extension_loaded('filter'))?die('filter missing!'):''; // filter inputs
// =============== SECURING COOKIES  - most of these get set by Session2DB if youre using that but in case you arent =============== // 
ini_set('session.cookie_httponly', '1'); // Prevent javascript access
ini_set('session.use_only_cookies', '1'); // prevents attacks involved passing session ids in URLs. 
ini_set('session.use_strict_mode', '1'); // Rejects uninitialized session IDs
if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
	|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){ini_set('session.cookie_secure', '1');} // https only
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']); // domain
ini_set('session.use_trans_sid', '0'); // disabling transparent session ID management improves the general session ID security by eliminating the possibility of a session ID injection and/or leak.
ini_set('session.sid_bits_per_character', '6'); // specify the number of bits in encoded session ID character. '4', '5', and '6' The more bits results in stronger session ID.
ini_set('session.sid_length', '96'); // Session ID length can be between 22 to 256. Longer session ID is harder to guess
ini_set('session.cookie_samesite', 'Strict'); //  provides some protection against cross-site request forgery attacks
ini_set('session.gc_maxlifetime', '3650'); // 3600 = 1 hour
ini_set('session.save_path', __DIR__ . '/config'); // know where your sessions are stored!
// 
session_name($config['session_name']);
date_default_timezone_set($config['timezone']);
require __DIR__ . '/views/mysqli.php';
require __DIR__ . '/views/session.php';
// If Session2DB not setup we start native php session
if(session_status() !== PHP_SESSION_ACTIVE) session_start();

require __DIR__ . '/router.php';

Route::add('/', function() {
    require __DIR__ . '/views/home.php';
});

Route::add('/login', function() {
    require __DIR__ . '/views/login.php';
});

Route::add('/register', function() {
    require __DIR__ . '/views/register.php';
});

Route::add('/logout', function() {
    require __DIR__ . '/views/logout.php';
});

Route::add('/item', function() {
    require __DIR__ . '/views/item.php';
});

Route::add('/products', function() {
    require __DIR__ . '/views/products.php';
});

Route::add('/profile', function() {
    require __DIR__ . '/views/profile.php';
});

Route::add('/orders', function() {
    require __DIR__ . '/views/orders.php';
});

Route::add('/order-details', function() {
    require __DIR__ . '/views/order-details.php';
});

Route::add('/cart', function() {
    require __DIR__ . '/views/cart.php';
});

Route::add('/shipping-calculator', function() {
    require __DIR__ . '/views/shipping-calculator.php';
});

Route::add('/checkout', function() {
    require __DIR__ . '/views/checkout.php';
});

Route::add('/cart-remove-item', function() {
    require __DIR__ . '/views/cart-remove-item.php';
});

Route::add('/confirmation', function() {
    require __DIR__ . '/views/confirmation.php';
});

Route::add('/faq', function() {
    require __DIR__ . '/views/faq.php';
});

Route::add('/contact', function() {
    require __DIR__ . '/views/contact.php';
});

Route::add('/about', function() {
    require __DIR__ . '/views/about.php';
});

Route::add('/privacy-policy', function() {
    require __DIR__ . '/views/privacy-policy.php';
});

Route::add('/forgot-password', function() {
    require __DIR__ . '/views/forgot-password.php';
});

Route::add('/reset', function() {
    require __DIR__ . '/views/reset.php';
});

Route::add('/sitemap', function() {
	Header('Content-Type: text/xml');
    require __DIR__ . '/views/sitemap.xml';
});

Route::add('/admin/home', function() {
    require __DIR__ . '/views/admin/home.php';
});

Route::add('/admin/login', function() {
    require __DIR__ . '/views/admin/login.php';
});

Route::add('/admin/logout', function() {
    require __DIR__ . '/views/admin/logout.php';
});

Route::add('/admin/reset-password', function() {
    require __DIR__ . '/views/admin/reset-password.php';
});

Route::add('/admin/products', function() {
    require __DIR__ . '/views/admin/products.php';
});

Route::add('/admin/customers', function() {
    require __DIR__ . '/views/admin/customers.php';
});

Route::add('/admin/orders', function() {
    require __DIR__ . '/views/admin/orders.php';
});

Route::add('/admin/faq', function() {
    require __DIR__ . '/views/admin/faq.php';
});

Route::add('/admin/settings', function() {
    require __DIR__ . '/views/admin/settings.php';
});

Route::add('/admin/products/create', function() {
    require __DIR__ . '/views/admin/create-product.php';
});

Route::add('/admin/customers/create', function() {
    require __DIR__ . '/views/admin/create-customer.php';
});

Route::add('/admin/faq/create', function() {
    require __DIR__ . '/views/admin/create-faq.php';
});

Route::add('/admin/stats', function() {
    require __DIR__ . '/views/admin/stats.php';
});

Route::add('/csp/report', function() {
    require __DIR__ . '/views/report.php';
});

Route::submit();