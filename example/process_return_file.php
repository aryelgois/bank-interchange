<?php

require_once __DIR__ . '/autoload.php';

use aryelgois\BankInterchange;

$controller = new BankInterchange\Controllers\ReturnFile($_POST['return_file']);

$result = $controller->validate();

if ($result && isset($_POST['apply'])) {
    $controller->apply();
    header('Location: .');
    die();
}

var_dump($result);
