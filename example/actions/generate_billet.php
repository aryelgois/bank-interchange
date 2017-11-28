<?php

require_once __DIR__ . '/../autoload.php';

use aryelgois\BankInterchange;
use aryelgois\Medools;

$data = json_decode(file_get_contents(__DIR__ . '/../data/billet.json'), true);
$logos = __DIR__ . '/../../res/logos';

$controller = new BankInterchange\Controllers\BankBillet(
    $_GET['id'],
    $data,
    $logos
);

$controller->output();
