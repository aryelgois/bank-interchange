<?php

require_once __DIR__ . '/../autoload.php';

if (!isset($_GET['state'])) {
    die;
}

$data = aryelgois\Medools\Models\Address\County::dump(
    [
        'state' => $_GET['state'],
        'ORDER' => 'name',
    ],
    [
        'id',
        'name',
    ]
);

$result = '';
foreach ($data as $county) {
    $result .= '<option value="' . $county['id'] . '">'
        . $county['name']
        . '</option>';
}
die($result);
