<?php

use aryelgois\BankInterchange as BankI;

require_once __DIR__ . '/autoload.php';

// new controler
$controller = new BankI\Controllers\BankBillet($db_address, $db_banki, $config);

//output result
if ($controller->execute()) {
    
} else {
    
}
echo '<p>END</p>';
