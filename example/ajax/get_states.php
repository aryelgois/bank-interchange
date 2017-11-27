<?php

require_once __DIR__ . '/../autoload.php';

if (!isset($_GET['country'])) {
    die;
}

$data = aryelgois\Medools\Models\Address\State::dump(
    [
        'country' => $_GET['country'],
        'ORDER' => 'code',
    ],
    [
        'id',
        'code',
        'name',
    ]
);

$result = '';
foreach ($data as $state) {
    $result .= '<option value="' . $state['id'] . '">'
             . $state['code'] . ' - '
             . $state['name']
             . '</option>';
}
die($result);
