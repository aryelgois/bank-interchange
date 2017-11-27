<?php

require_once __DIR__ . '/../autoload.php';

$iterator = new aryelgois\Medools\ModelIterator(
    'aryelgois\\BankInterchange\\Models\\Title'
);

$template = '<tr>' . str_repeat('<td>%s</td>', 7) . '</tr>';

$already = array_column(
    aryelgois\BankInterchange\Models\ShippingFileTitle::dump([], ['title']),
    'title'
);

$result = '';
foreach ($iterator as $model) {
    $id = $model->id;

    $data = [
        (in_array($id, $already) ? '' : '<input name="titles[]" value="' . $id . '" type="checkbox" />'),
        $id,
        format_model_pretty($model->payer),
        format_model_pretty($model->assignor),
        $model->specie->format($model->value),
        $model->stamp,
        '<a href="actions/generate_billet.php?id=' . $id . '">pdf</a>',
    ];

    $result .= sprintf($template, ...$data);
}
die($result);
