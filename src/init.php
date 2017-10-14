<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('lib/functions.php');

use App;

\App\Configuration::set('DB_HOST', '127.0.0.1');
\App\Configuration::set('DB_DB', 'av');
\App\Configuration::set('DB_PORT', '3306');
\App\Configuration::set('DB_USER', 'root');
\App\Configuration::set('DB_PWD', 'keins');
\App\Configuration::set('DB_PREFIX', 'av');

$router = App\Router::getInstance();
$router->addRoute('all', 'p:(/$|/home$)', array('Controller', 'PageController', 'home'));
$router->addRoute('all', 'p:(/product)', array('Controller', 'PageController', 'products'));
$router->addRoute('all', 'customers', array('Controller', 'PageController', 'customers'));

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