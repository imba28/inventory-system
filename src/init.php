<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once('lib/functions.php');
require_once(ABS_PATH . '/config.php');

use App;

$router = App\Router::getInstance();
$router->addRoute('all', array('/', '/home', '/products/return'), array('Controller', 'PageController', 'home'));
$router->addRoute('all', array('/products/:action', '/products'), array('Controller', 'PageController', 'products'));
$router->addRoute('all', array('/product/:id'), array('Controller', 'PageController', 'product'));
$router->addRoute('all', '/customers', array('Controller', 'PageController', 'customers'));
$router->addRoute('post', '/inventur', array('Controller', 'InventurController', 'actionInventur'));
$router->addRoute('all', '/inventur/:action', array('Controller', 'InventurController', 'main'));

$router->addRoute('get', '*', array('Controller', 'FileController', 'main'));

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