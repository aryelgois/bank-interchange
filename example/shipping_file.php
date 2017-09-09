<?php

use aryelgois\BankInterchange as BankI;
use aryelgois\BankInterchange\example\Database as Db;

// debug
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

// autoload
require __DIR__ . '/../vendor/autoload.php';

// connect to databases
include __DIR__ . '/Database.php';
$db_config = __DIR__ . '/database_config.json';
$db_address = new Db($db_config, 'address');
$db_banki = new Db($db_config, 'banki');

// load config file
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

// new controler
$controller = new BankI\Controllers\Controller($db_address, $db_banki, $config);

//output result
if ($controller->execute()) {
    $filename = $controller->saveFile(__DIR__ . '/data/shipping_files');
    if ($filename != false) {
        echo '<h2>' . $filename . "</h2>\n";
        echo '<pre>' . $controller->result . "</pre>\n\n\n";
    } else {
        echo '<p>Error saving file. Remember to give write permission to apache at data/*</p>';
    }
} else {
    echo '<p>There are no titles to send</p>';
}
echo '<p>END</p>';
