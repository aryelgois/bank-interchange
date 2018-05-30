<?php

require_once __DIR__ . '/../bootstrap.php';

use aryelgois\MedoolsRouter;
use Symfony\Component\Yaml\Yaml;

$request = from_globals();

$router_data = Yaml::parseFile(APP_ROOT . '/config/router.yml');

$controller = new MedoolsRouter\Controller(
    $request['url'],
    $router_data['resources'],
    $router_data['configurations']
);

$controller->run(
    $request['method'],
    $request['uri'],
    $request['headers'],
    $request['body']
);
