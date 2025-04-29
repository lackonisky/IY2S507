<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


echo "The value of PASS_INSERT is: " . $_ENV['servername'];


print_r(password_algos());
session_start();
//$_SESSION["Active"] = 1;
session_destroy();

?>