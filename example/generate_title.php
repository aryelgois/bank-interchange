<?php

require_once __DIR__ . '/autoload.php';

use aryelgois\BankInterchange;
use aryelgois\Medools;

$title = new BankInterchange\Models\Title;

$due = $title->getCurrentTimestamp();
$due = date('Y-m-d', strtotime($due . ' + 30 days'));

$title->set('assignor', $_POST['assignor']);
$title->set('payer', $_POST['payer']);
$title->set('specie', 1);
$title->set('doc_type', 1);
$title->set('kind', 99);
$title->set('value', $_POST['value']);
$title->set('iof', 0);
$title->set('rebate', 0);
$title->set('fine_type', 3);
$title->set('discount_type', 3);
$title->set('description', 'Teste de Boleto');
$title->set('due', $due);
$title->setOurNumber();

if (!$title->save()) {
    die('Error saving Title in the Database');
}

header('Location: .');
