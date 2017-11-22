<?php

require_once __DIR__ . '/autoload.php';

use aryelgois\BankInterchange;

$return_file = BankInterchange\Controllers\ReturnFile::process($_POST['return_file']);

var_dump($return_file);
