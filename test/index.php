<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../vendor/autoload.php";


$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env');
$dotenv->load();
$dotenv->required(['DEV', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_LUSER', 'DB_LNAME'])->notEmpty();
$dotenv->required("DEV")->allowedValues(['DEV', 'PRODUCTION']);



use Database\DB;

$db = new DB();


$result = $db->select("*", "albumNames");
echo "<h1>Select</h1>";
echo "<pre>";
print_r($result);
echo "</pre>";

$allResult = $db->selectAll("*", "albumNames");
echo "<h1>SelectAll</h1>";
echo "<pre>";
print_r($allResult);


echo "</pre>";
