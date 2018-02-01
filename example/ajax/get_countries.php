<?php

require_once __DIR__ . '/../autoload.php';

$data = aryelgois\Medools\Models\Address\Country::dump(
    [
        'ORDER' => 'code_a2',
    ],
    [
        'id',
        'code_a2',
        'name_en',
        'name_local',
    ]
);

$result = '';
foreach ($data as $country) {
    $result .= '<option value="' . $country['id'] . '"'
        . ($country['name_en'] == 'Brazil' ? ' selected' : '') . '>'
        . $country['code_a2'] . ' - '
        . ($country['name_local'] ?? $country['name_en'])
        . '</option>';
}
die($result);
