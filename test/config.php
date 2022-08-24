<?php

 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);

 require_once dirname(__DIR__) . '/vendor/autoload.php';

 $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env');
 $dotenv->load() ;
 $dotenv->required(['DEV', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_LUSER', 'DB_LNAME'])->notEmpty();
 $dotenv->required('DEV')->allowedValues(['DEV', 'PRODUCTION']);
