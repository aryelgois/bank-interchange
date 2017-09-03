<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

// debug
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

use aryelgois\cnab240\example;

require_once __DIR__ . '/../vendor/autoload.php';

// Admin Controller
$ctrl = new example\controller\Admin();
