<?php
$router->addRoute('all', array('/', '/home', '/products/return'), 'ProductController#home');

$router->addRoute('delete', '/product/:id', 'ProductController#delete');
$router->addRoute('all', array('/product/:id', '/product/:id/:action'), 'ProductController#product');

$router->addRoute('get', array('/products/category/:category', '/products/category/:category/:page'), 'ProductController#displayCategory');
$router->addRoute('get', '/products/category', 'ProductController#displayCategories');
$router->addRoute('get', array('/products/search', '/products/:action/:page'), 'ProductController#search');
$router->addRoute('all', array('/products/:action', '/products'), 'ProductController#products');

$router->addRoute('get', '/customers/new', 'CustomerController#new');
$router->addRoute('get', ['/customers', '/customers/:page'], 'CustomerController#index');
$router->addRoute('post', '/customers', 'CustomerController#create');

$router->addRoute('get', '/customer/:_id', 'CustomerController#show');
$router->addRoute('get', '/customer/:_id/edit', 'CustomerController#edit');
$router->addRoute('put', '/customer/:id', 'CustomerController#update');
$router->addRoute('delete', '/customer/:id', 'CustomerController#delete');
//$router->addRoute('all', '/customer/:id/:action', 'CustomerController#action');

$router->addRoute('post', '/inventur', 'InventurController#actionInventur');
$router->addRoute('get', '/inventur/:_id', 'InventurController#show');
$router->addRoute('get', '/inventur/list', 'InventurController#list');
$router->addRoute('get', array('/inventur/:action', '/inventur'), 'InventurController#main');

$router->addRoute('get', '/login', 'SessionController#loginForm');
$router->addRoute('post', '/login', 'SessionController#login');
$router->addRoute('post', '/logout', 'SessionController#logout');

$router->addRoute('get', '/logs', 'LogController#index');

$router->addRoute('delete', '/images/:id', 'ProductImageController#delete');

$router->addRoute('get', '*', 'FileController#main');
