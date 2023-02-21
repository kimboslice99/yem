<?php
header('Content-Security-Policy: anything');


$config = parse_ini_file(__DIR__ . '/bin/config.ini');

phpinfo();
?>