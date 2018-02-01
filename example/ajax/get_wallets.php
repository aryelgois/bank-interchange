<?php

require_once __DIR__ . '/../autoload.php';

$data = aryelgois\BankInterchange\Models\Wallet::dump(
    [
        'ORDER' => 'febraban',
    ],
    [
        'id',
        'symbol',
        'name',
    ]
);

$result = '';
foreach ($data as $wallet) {
    $result .= '<option value="' . $wallet['id'] . '">'
        . $wallet['symbol'] . ' - '
        . $wallet['name']
        . '</option>';
}
die($result);
