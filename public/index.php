<?php

require_once __DIR__ . '/../vars.php';
require_once __DIR__ . '/../src/lib/functions.php';

require ABS_PATH . '/vendor/autoload.php';
require_once ABS_PATH . '/src/config/config.php';

use App\Bootstrap\Bootstrap;
use Symfony\Component\HttpFoundation\Request;

$bootstrap = new Bootstrap();
$bootstrap->startUp();

$request = Request::createFromGlobals();
$kernel = $bootstrap->getKernel();
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
