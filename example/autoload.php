<?php

// debug
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

// autoload
require_once __DIR__ . '/../vendor/autoload.php';

use aryelgois\Medools;
use aryelgois\BankInterchange;

// Medools
Medools\MedooConnection::loadConfig(__DIR__ . '/../config/medools.php');

// session
session_start();

// common functions

function format_model_pretty($model, $html = true)
{
    $person = $model->person;
    $info = ($model instanceof BankInterchange\Models\Assignor)
          ? 'Account: ' . $model->formatAgencyAccount(4, 11)
          : $person->documentFormat(true);

    $result = $person->name
            . ($html ? '<br/><small>' : ' (')
            . $info
            . ($html ? '</small>' : ')');

    return $result;
}
