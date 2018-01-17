<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once('lib/functions.php');
require_once(ABS_PATH . '/config.php');

$router = App\Router::getInstance();
include(ABS_PATH . '/src/config/routes.php');

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