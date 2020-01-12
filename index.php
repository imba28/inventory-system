<?php
require_once('vars.php');
require_once('src/init.php');

try {
    $bootstrap = new \App\Bootstrap\Bootstrap();
    $bootstrap->run();
} catch (\Exception $e) {
    echo $e->getMessage();
}
