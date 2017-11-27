<?php

require_once __DIR__ . '/../autoload.php';

use aryelgois\BankInterchange;

$cnab = $_GET['cnab'] ?? '';
$id = $_GET['id'];

$controller = new BankInterchange\Controllers\Cnab($cnab, $id);

echo $controller->output();
