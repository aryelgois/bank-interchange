<?php

use aryelgois\BankInterchange\example\Database as Db;

// debug
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

// autoload
require_once __DIR__ . '/../vendor/autoload.php';

// connect to databases
include __DIR__ . '/Database.php';
$db_config = __DIR__ . '/database_config.json';
$db_address = new Db($db_config, 'address');
$db_banki = new Db($db_config, 'banki');
