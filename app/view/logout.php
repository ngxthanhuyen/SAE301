<?php
include_once __DIR__  . '/../config/config.php';


session_unset();
session_destroy();

header('Location: ?page=login_form');
exit;
?>