<?php

require_once __DIR__ . '/../autoload.php';

use aryelgois\BankInterchange;

$title = new BankInterchange\Models\Title;

$due = $title->getCurrentTimestamp();
$due = date('Y-m-d', strtotime($due . ' + 30 days'));

$title->setMultiple([
    'assignor'      => $_POST['assignor'],
    'payer'         => $_POST['payer'],
    'specie'        => 1,
    'doc_type'      => 1,
    'kind'          => 99,
    'value'         => $_POST['value'],
    'iof'           => 0,
    'rebate'        => 0,
    'fine_type'     => 3,
    'discount_type' => 3,
    'description'   => 'Teste de Boleto',
    'due'           => $due,
]);
$title->setOurNumber();

if (!$title->save()) {
    die('Error saving Title in the Database');
}

header('Location: ..');
