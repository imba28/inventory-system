<?php
$router->addRoute('all', array('/', '/home', '/products/return'), 'ProductController#home');

$router->addRoute('post', '/product/:id/delete', 'ProductController#delete');
$router->addRoute('all', array('/product/:id', '/product/:id/:action'), 'ProductController#product');

$router->addRoute('all', array('/products/category/:category', '/products/category/:category/:page'), 'ProductController#displayCategory');
$router->addRoute('all', array('/products/search', '/products/:action/:page'), 'ProductController#search');
$router->addRoute('all', array('/products/:action', '/products'), 'ProductController#products');

$router->addRoute('all', '/customers', 'CustomerController#customers');
$router->addRoute('all', '/customers/:action', 'CustomerController#action');
$router->addRoute('all', '/customer/:id', 'CustomerController#customer');
$router->addRoute('post', '/customer/:id/delete', 'CustomerController#delete');
$router->addRoute('all', '/customer/:id/:action', 'CustomerController#action');

$router->addRoute('post', '/inventur', 'InventurController#actionInventur');
$router->addRoute('get', '/inventur/:_id', 'InventurController#show');
$router->addRoute('get', array('/inventur/:action', '/inventur'), 'InventurController#main');

$router->addRoute('get', '/login', 'SessionController#loginForm');
$router->addRoute('post', '/login', 'SessionController#login');
$router->addRoute('post', '/logout', 'SessionController#logout');

$router->addRoute('get', '*', 'FileController#main');
?>