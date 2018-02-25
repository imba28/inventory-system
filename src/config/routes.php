<?php
$router->addRoute('all', array('/', '/home', '/products/return'), 'ProductController#home');

$router->resource('product', 'ProductController');

$router->addRoute(['get','post'], '/product/:id/rent', 'ProductController#rent');
$router->addRoute(['get','post'], '/product/:id/return', 'ProductController#return');
$router->addRoute(['get','post'], '/product/:id/request', 'ProductController#request');
$router->get('/products/:_page', 'ProductController#index');
$router->addRoute(['get','post'], '/products/rent', 'ProductController#rentMask');
$router->get(array('/products/category/:category', '/products/category/:category/:page'), 'ProductController#displayCategory');
$router->get('/products/category', 'ProductController#displayCategories');
$router->addRoute(['get', 'post'], array('/products/search', '/products/search/:page'), 'ProductController#search');

$router->resource('customer', 'CustomerController');
$router->get('/customers/:page', 'CustomerController#index');

$router->post('/inventur', 'InventurController#actionInventur');
$router->get('/inventur/:_id', 'InventurController#show');
$router->get('/inventur/list', 'InventurController#list');
$router->get(array('/inventur/:action', '/inventur'), 'InventurController#main');

$router->get('/login', 'SessionController#loginForm');
$router->post('/login', 'SessionController#login');
$router->post('/logout', 'SessionController#logout');

$router->get('/logs', 'LogController#index');

$router->delete('/images/:id', 'ProductImageController#delete');

$router->get('*', 'FileController#main');
