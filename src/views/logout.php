<?php

unset($_SESSION['name']);
unset($_SESSION['firstname']);
unset($_SESSION['lastname']);
unset($_SESSION['email']);
unset($_SESSION['phone']);
unset($_SESSION['address']);
unset($_SESSION['created-time']);
unset($_SESSION['id']);
header('Location: /');