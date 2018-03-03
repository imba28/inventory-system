<?php
namespace App;

class Inventur
{
    private $inventurObject;
    private $inventurActions = array();

    public function __construct()
    {
        try {
            $this->inventurObject = Models\Inventur::findByFilter(
                array(
                'finishDate', 'IS', 'NULL'
                ),
                1
            );
        } catch (\App\Exceptions\NothingFoundException $e) {
            $this->inventurObject = Models\Inventur::new();
        }

        if ($this->isStarted()) {
            $this->loadInventurActions();
        }
    }

    public function start(\App\Models\User $startedBy)
    {
        if (!$this->isStarted()) {
            $this->inventurObject->set('user', $startedBy);
            $this->inventurObject->set('startDate', 'NOW()');
            return $this->inventurObject->save();
        }

        return false;
    }

    public function end()
    {
        if ($this->isStarted()) {
            if (count($this->itemsMissing) == 0) {
                $this->inventurObject->set('finishDate', 'NOW()');
                return $this->inventurObject->save();
            } else {
                throw new \App\Exceptions\InventurNotFinishedException(
                    count($this->itemsMissing). " Produkte wurden noch nicht erfasst!"
                );
            }
        } else {
            throw new \App\Exceptions\InvalidOperationException("Inventur wurde noch nicht gestartet!");
        }
    }

    public function isStarted()
    {
        return $this->inventurObject->isStarted();
    }

    public function isReady()
    {
        return count($this->inventurObject->itemsNotScanned()) === 0;
    }

    public function getMissingItems()
    {
        return $this->inventurObject->itemsMissing();
    }

    public function getRegisteredItems()
    {
        return $this->inventurObject->itemsScanned();
    }

    public function getNotRegisteredItems()
    {
        return $this->inventurObject->itemsNotScanned();
    }

    public function getModel()
    {
        return $this->inventurObject;
    }

    public function getInventurActions()
    {
        return $this->inventurActions;
    }

    public function getTotalCount()
    {
        return count($this->inventurObject->items());
    }

    public function get($key)
    {
        return $this->inventurObject->get($key);
    }

    public static function getLastInventur()
    {
        try {
            return Models\Inventur::findByFilter(
                array(
                'finishDate', 'IS NOT', 'NULL'
                ),
                1,
                array('id' => 'DESC')
            );
        } catch (\App\Exceptions\NothingFoundException $e) {
            return null;
        }
    }

    public function registerProduct(\App\Models\Product $product)
    {
        if (($key = array_search($product, $this->inventurObject->itemsScanned()->toArray())) === false) {
            $inventurProduct = $this->getInventurAction($product);
            $inventurProduct->set('in_stock', '1');
            
            if ($inventurProduct->save(true)) {
                return true;
            }

            return false;
        }
        throw new \App\QueryBuilder\NothingChangedException('already scanned!');
    }


    public function missingProduct(\App\Models\Product $product)
    {
        
        if (($key = array_search($product, $this->inventurObject->itemsScanned()->toArray())) === false) {
            $inventurProduct = $this->getInventurAction($product);
            $inventurProduct->set('in_stock', '1');
            $inventurProduct->set('missing', '1');
            
            if ($inventurProduct->save(true)) {
                return true;
            }

            return false;
        }
        
        throw new \App\QueryBuilder\NothingChangedException('already scanned!');
    }

    private function loadInventurActions()
    {
        foreach (Models\Product::all() as $product) {
            $inventurAction = $this->getInventurAction($product);
            $this->inventurActions[$product->getId()] = $inventurAction;
        }
    }

    private function getInventurAction(Models\Product $product)
    {
        try {
            $action = Models\InventurProduct::findByFilter(
                array(
                    array('product', '=', $product),
                    array('inventur', '=', $this->inventurObject)
                ),
                1
            );
        } catch (\App\Exceptions\NothingFoundException $e) {
            $action = Models\InventurProduct::new();
            $action->set('product', $product);
            $action->set('inventur', $this->inventurObject);
            $action->set('user', $this->inventurObject->get('user'));

            $action->save();
        }

        return $action;
    }
}
