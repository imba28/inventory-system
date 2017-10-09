<?php
require_once('vars.php');
require_once('src/init.php');

try {
    /*$product = App\Models\Product::new();
    $product->set('name','Playstation 3');
    $product->set('type', 'Konsole');
    $product->save();

    $products = App\Models\Product::grabAll(1, 'user_id');

    vd($products);

    die();

    $action = new App\Rental\Action($product, $customer);
    $action->save();*/

    $router = App\Router::getInstance();
    $router->addRoute('get', 'p:(/$|/home$)', function() {
        return 'Hello there.';
    });

    $router->addRoute('get', '/customers', array('Controller', 'PageController', 'home'));


    vd($router->route());
}
catch(Exception $e) {
    echo $e->getMessage();
}
?>