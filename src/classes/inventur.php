<?php
namespace App;

class Inventur {
    private $itemsRented = array();
    private $itemsAvailable = array();

    private $itemsMissing = array();
    private $itemsRegistered = array();

    private $totalItems = 0;
    private $inventurObject;

    public function __construct() {
        try {
            $this->inventurObject = current(Models\Inventur::grabByFilter(array(
                'finishDate', 'IS', 'NULL'
            ), 1));
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $this->inventurObject = Models\Inventur::new();
        }

        foreach(Models\Product::grabAll() as $product) {
            if($product->isAvailable()) {
                $this->itemsAvailable[] = $product;
            }
            else {
                $this->itemsRented[] = $product;
            }

            $this->totalItems++;

            $inventurProduct = $this->getInventurAction($product);
            if($inventurProduct->isInStock()) {
                $this->itemsRegistered[] = $product;
            }
            else {
                $this->itemsMissing[] = $product;
            }
        }
    }

    private function getInventurAction(Models\Product $product) {
        try {
            $action = current(Models\InventurProduct::grabByFilter(array(
                array('product', '=', $product),
                array('inventur', '=', $this->inventurObject)
            )));
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $action = Models\InventurProduct::new();
            $action->set('product', $product);
            $action->set('inventur', $this->inventurObject);

            $action->save();
        }

        return $action;
    }

    public function start() {
        if(!$this->isStarted()) {
            /*
            vd("RENTED: " . count($this->itemsRented));
            vd("AVAILABLE: " . count($this->itemsAvailable));
            */

            $this->inventurObject->set('startDate', 'NOW()');
            return $this->inventurObject->save();
        }

        return false;
    }

    public function end() {
        if($this->isStarted()) {
            if(count($this->itemsMissing) == 0) {
                $this->inventurObject->set('finishDate', 'NOW()');
                return $this->inventurObject->save();
            }
            else throw new \App\Exceptions\InventurNotFinishedException(count($this->itemsMissing). " Produkte wurden noch nicht erfasst!");
        }
        else throw new \App\Exceptions\InvalidOperationException("Inventur wurde noch nicht gestartet!");
    }

    public function isStarted() {
        return $this->inventurObject->isStarted();
    }

    public function isReady() {
        return count($this->itemsMissing) === 0;
    }

    public function getMissingItems() {
        return $this->itemsMissing;
    }

    public function getRegisteredItems() {
        return $this->itemsRegistered;
    }

    public function getModel() {
        return $this->inventurObject;
    }

    public function getTotalCount() {
        return $this->totalItems;
    }

    public function get($key) {
        return $this->inventurObject->get($key);
    }

    public static function getLastInventur() {
        try {
            return current(Models\Inventur::grabByFilter(array(
                'finishDate', 'IS NOT', 'NULL'
            ), 1, array('id' => 'DESC')));
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            return null;
        }
    }

    public function registerProduct(\App\Models\Product $product) {
        $inventurProduct;

        try {
            $inventurProduct = \App\Models\inventurProduct::grab($product->getId(), 'product_id');

            if($inventurProduct->isInStock()) {
                throw new \App\QueryBuilder\NothingChangedException("already scanned!");
            }
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $inventurProduct = \App\Models\inventurProduct::new();
            $inventurProduct->set('product', $product);
        }

        $inventurProduct->set('in_stock', '1');
        $inventurProduct->set('inventur', $this->inventurObject);

        function indexOf($array, $product) {
            foreach($array as $key => $value) {
                if($value->getId() === $product->getId()) {
                    return $key;
                }
            }
            return false;
        }

        if($inventurProduct->save()) {
            if(($key = indexOf($this->itemsMissing, $product)) !== FALSE) {
                unset($this->itemsMissing[$key]);
                $this->itemsMissing = array_values($this->itemsMissing);

                $this->itemsRegistered[] = $product;
                return true;
            }

            return false;
        }
        return false;
    }
}
?>