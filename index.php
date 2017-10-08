<?php
require_once('vars.php');
require_once('src/init.php');

try {
    $customer = App\Models\Customer::get(1);
    $product = App\Models\Product::get(1);

    vd($customer);
    vd($product);

    $action = new App\Rental\Action($product, $customer);
    var_dump($action->isProductReturned());
    $action->returnProduct();
    var_dump($action->isProductReturned());
}
catch(Exception $e) {
    echo $e->getMessage();
}
?>