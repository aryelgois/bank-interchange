<?php

require_once __DIR__ . '/autoload.php';

use aryelgois\BankInterchange;

$return_file = BankInterchange\Controllers\ReturnFile::process($_POST['return_file']);

$messages = $return_file->getMessages();

$info = "<ul>\n";
foreach ($messages['info'] as $m) {
    $text = implode("<br />\n", array_filter([
        'Our Number: ' . $m['our_number'],
        (isset($m['movement']) ? 'Movement: ' . $m['movement'] : ''),
        'Occurrence: ' . $m['occurrence'],
        (isset($m['occurrence_date']) ? 'Occurrence date: ' . $m['occurrence_date'] : ''),
    ]));
    $info .= '<li><p>' . $text . "</p></li>\n";
}
$info .= "</ul>\n\n";

$count = count($messages['error']);
$error = '<strong>' . $count . ' errors' . ($count > 0 ? ':' : '') . "</strong>\n<ul>\n";
if ($count) {
    $error .= '<li>' . implode("</li>\n<li>", $messages['error']) . "</li>\n";
}
$error .= "</ul>\n\n";

$count = count($messages['warning']);
$warning = '<strong>' . $count . ' warnings' . ($count > 0 ? ':' : '') . "</strong>\n<ul>\n";
if ($count) {
    $warning .= '<li>' . implode("</li>\n<li>", $messages['warning']) . "</li>\n";
}
$warning .= "</ul>\n\n";

echo "<h2>Result:</h2>\n\n" . $info . $error . $warning;

if (isset($_POST['apply'])) {
    echo "<h2>Applying...</h2>\n";
    $result = $return_file->apply();
    if ($result === false) {
        echo 'Nothing to apply';
    } elseif ($result === true) {
        echo 'Applied successfully';
    } else {
        echo 'Titles which failed: ' . implode(', ', $result);
    }
}
