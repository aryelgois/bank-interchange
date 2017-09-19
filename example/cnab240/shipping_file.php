<?php

use aryelgois\BankInterchange as BankI;

require_once __DIR__ . '/../autoload.php';

$config = [
    'assignor' => 1 // use assignor #1 from Database
];

// new controler
$controller = new BankI\Cnab240\Controllers\ShippingFile($db_address, $db_banki, $config);

// output result
if ($controller->execute()) {
    $filename = $controller->save(__DIR__ . '/../data/cnab240/shipping_files');
    if ($filename != false) {
        echo '<h2>' . $filename . "</h2>\n";
        echo '<pre>' . $controller->result . "</pre>\n\n\n";
    } else {
        echo '<p>Error saving file. Remember to give write permission to apache at data/*</p>';
    }
} else {
    echo '<p>There are no titles to send</p>';
}
echo '<p>END</p>';
