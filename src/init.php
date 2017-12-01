<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once('lib/functions.php');
require_once(ABS_PATH . '/config.php');

use App;

$router = App\Router::getInstance();
$router->addRoute('all', 'p:(/$|/home$|/products/return$)', array('Controller', 'PageController', 'home'));
$router->addRoute('all', 'p:(/product)', array('Controller', 'PageController', 'products'));
$router->addRoute('all', 'customers', array('Controller', 'PageController', 'customers'));
$router->addRoute('post', 'inventur', array('Controller', 'InventurController', 'actionInventur'));
$router->addRoute('all', 'p:(/inventur)', array('Controller', 'InventurController', 'main'));

\App\Menu::getInstance()->set('items', array(
    'Produkte' => 'products',
    'Kunden' => 'customers',
    'Inventur' => 'inventur'
));

try {
    \App\Registry::setDatabase(new \App\Database());
}
catch(Exception $e){
    die("Keine Verbindung zur Datenbank möglich:". $e->getMessage());
}
?>