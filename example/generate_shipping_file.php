<?php

require_once __DIR__ . '/autoload.php';

use aryelgois\BankInterchange;

BankInterchange\Controllers\ShippingFile::create($_POST['titles'] ?? []);

header('Location: .');
