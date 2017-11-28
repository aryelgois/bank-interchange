<?php

require_once __DIR__ . '/../autoload.php';

$data = aryelgois\BankInterchange\Models\Bank::dump(
    [
        'ORDER' => 'code',
    ],
    [
        'id',
        'code',
        'name',
    ]
);

$result = '';
foreach ($data as $bank) {
    $result .= '<option value="' . $bank['id'] . '">'
             . $bank['code'] . ' - '
             . $bank['name']
             . '</option>';
}
die($result);
