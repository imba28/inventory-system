<?php
require_once('vars.php');
require_once('src/init.php');

try {
    $router->route();
} catch(\Exception $e) {
    echo $e->getMessage();
}
?>