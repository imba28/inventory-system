<?php
$router->addRoute('all', array('/', '/home', '/products/return'), 'ProductController#home');

$router->addRoute('all', array('/product/:id', '/product/:id/:action'), 'ProductController#product');

$router->addRoute('all', array('/products/category/:category', '/products/category/:category/:page'), 'ProductController#displayCategory');
$router->addRoute('all', array('/products/search', '/products/:action/:page'), 'ProductController#search');
$router->addRoute('all', array('/products/:action', '/products'), 'ProductController#products');

$router->addRoute('all', '/customers', 'ProductController#customers');

$router->addRoute('post', '/inventur', 'InventurController#actionInventur');
$router->addRoute('get', array('/inventur/:action', '/inventur'), 'InventurController#main');

$router->addRoute('get', '*', 'FileController#main');
?>