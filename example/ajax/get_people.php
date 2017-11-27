<?php

require_once __DIR__ . '/../autoload.php';

if (!in_array($_GET['class'] ?? null, ['Payer', 'Assignor'])) {
    die;
}

$iterator = new aryelgois\Medools\ModelIterator(
    'aryelgois\\BankInterchange\\Models\\' . $_GET['class']
);

$result = '';
foreach ($iterator as $model) {
    $result .= printf(
        '<option value="%s">%s</option>',
        $model->id,
        format_model_pretty($model, false)
    );
}
die($result);
