<?php

use aryelgois\BankInterchange as BankI;

require_once __DIR__ . '/autoload.php';

// new controler
$controller = new BankI\Controllers\ShippingFile($db_address, $db_banki, $config);

//output result
if ($controller->execute()) {
    $filename = $controller->saveFile(__DIR__ . '/data/shipping_files');
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
