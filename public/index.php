<?php

require_once __DIR__ . '/../vars.php';
require_once __DIR__ . '/../src/lib/functions.php';

require ABS_PATH . '/vendor/autoload.php';
require_once ABS_PATH . '/src/config/config.php';

use App\Bootstrap\Bootstrap;
use App\Configuration;
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$kernel = new Kernel(Configuration::get('env'), Configuration::get('env') === 'dev');
$kernel->boot();

$bootstrap = $kernel->getContainer()->get(Bootstrap::class);
$bootstrap->startUp();

$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
