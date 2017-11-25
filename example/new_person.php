<?php

require_once __DIR__ . '/autoload.php';

use aryelgois\Utils;
use aryelgois\Medools;
use aryelgois\BankInterchange;

/*
 * Create Person
 */
$person = new Medools\Models\Person;
$person->setMultiple([
    'name'     => $_POST['name'],
    'document' => preg_replace('/[^\d]/', '', $_POST['document']),
]);

if (!$person->save()) {
    die('Error saving Person in the Database');
}

/*
 * Create Address
 */
$address = new BankInterchange\Models\FullAddress;
$address->setMultiple([
    'county'       => $_POST['county'],
    'neighborhood' => $_POST['neighborhood'],
    'place'        => $_POST['place'],
    'number'       => $_POST['number'],
    'zipcode'      => preg_replace('/[^\d]/', '', $_POST['zipcode']),
    'detail'       => $_POST['detail'] ?? '',
]);

if (!$address->save()) {
    die('Error saving Address in the Database');
}

/*
 * Create Assignor/Payer
 */
$is_assignor = $_POST['person_type'] == 'assignor';
$model = ($is_assignor)
       ? new BankInterchange\Models\Assignor
       : new BankInterchange\Models\Payer;

$model->person = $person;
$model->address = $address;

if ($is_assignor) {
    $model->setMultiple([
        'bank'       => $_POST['bank'],
        'wallet'     => $_POST['wallet'],
        'covenant'   => $_POST['covenant'],
        'agency'     => $_POST['agency'],
        'agency_cd'  => $_POST['agency_cd'],
        'account'    => $_POST['account'],
        'account_cd' => $_POST['account_cd'],
        'edi'        => $_POST['edi'],
        'logo'       => $_POST['logo'] ?? null,
        'url'        => $_POST['url'] ?? null,
    ]);
}

if (!$model->save()) {
    die('Error saving ' . ($is_assignor ? 'Assignor' : 'Payer') . ' in the Database');
}

header('Location: .');
