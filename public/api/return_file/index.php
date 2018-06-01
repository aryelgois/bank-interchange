<?php

require_once __DIR__ . '/../../bootstrap.php';

use aryelgois\MedoolsRouter;
use aryelgois\BankInterchange;
use Symfony\Component\Yaml\Yaml;

BankInterchange\ReturnFile\Parser::setConfigPath(APP_ROOT . '/config/return_file');

$request = from_globals();

$router_data = Yaml::parseFile(APP_ROOT . '/config/router.yml');

$controller = new MedoolsRouter\Controller(
    $request['url'],
    $router_data['resources'],
    $router_data['configurations'],
    BankInterchange\ReturnFile\Router::class
);

$controller->run(
    $request['method'],
    '/',
    $request['headers'],
    $request['body']
);
