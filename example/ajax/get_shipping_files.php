<?php

require_once __DIR__ . '/../autoload.php';

$iterator = new aryelgois\Medools\ModelIterator(
    'aryelgois\\BankInterchange\\Models\\ShippingFile'
);

$template = '<tr>' . str_repeat('<td>%s</td>', 5) . '</tr>';

$result = '';
foreach ($iterator as $shipping_file) {
    $id = $shipping_file->id;
    $titles = [];
    $total = 0.0;

    $shipping_file_titles = new aryelgois\Medools\ModelIterator(
        'aryelgois\\BankInterchange\\Models\\ShippingFileTitle',
        ['shipping_file' => $id]
    );
    foreach ($shipping_file_titles as $sft) {
        $title = $sft->title;
        $titles[] = $title->id;
        $total += (float) $title->value;
    }

    $links = '<a target="_blank" href="actions/generate_cnab.php?cnab=240&id=' . $id . '">CNAB240</a> '
        . '<a target="_blank" href="actions/generate_cnab.php?cnab=400&id=' . $id . '">CNAB400</a>';

    $data = [
        $id,
        implode(', ', $titles),
        $title->currency->format($total),
        $shipping_file->stamp,
        $links,
    ];

    $result .= sprintf($template, ...$data);
}
die($result);
