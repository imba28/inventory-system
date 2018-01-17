<?php
namespace App\Controller;

class PageController extends \App\BasicController implements \App\Interfaces\Controller {
    public function product() {
        $product = \App\Models\Product::grab($this->request->getParam('id'));

        $this->view->assign('product', $product);

        if($this->request->issetParam('sub')) {
            if($this->request->getParam('sub') == 'edit') {
                if($this->request->issetParam('submit')) {
                    $params = $this->request->getParams();
                    foreach($params as $key) {
                        $product->set($key, $this->request->getParam($key));
                    }

                    try {
                        $product->save();
                        \App\System::getInstance()->addMessage('success', $product->get('name'). ' wurde aktualisiert!');
                    }
                    catch(\App\QueryBuilder\NothingChangedException $e) {
                        //\App\System::getInstance()->addMessage('info', 'Es wurde nichts geändert.');
                    }
                    catch(\Exception $e) {
                        \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern!');
                    }
                }

                if($this->request->issetFile("add-productImage")) {
                    $files = $this->request->getFiles("add-productImage");
                    $error = false;

                    foreach($files as $file) {
                        $image = new \App\File\Image($file);

                        if($image->isValid()) {
                            if($image->save("/images")) {
                                $product_image = \App\Models\ProductImage::new();
                                $product_image->set('src', "/public/files/images/{$image->getDestination()}");
                                $product_image->set('product', $product);
                                $product_image->set('title', $image->getInfo('name'));
                                if($product_image->save()) {
                                    $product->addImage($product_image);
                                }
                            }
                            else {
                                $error = true;
                                \App\System::getInstance()->addMessage('error', "Fehler beim Speichern von {$image->getInfo('name')}");
                            }
                        }
                        else {
                            $error = true;
                            if(get_class($image->getError()) !== 'App\File\NoFileSentException') {
                                \App\System::getInstance()->addMessage('error', $image->getError()->getMessage());
                            }
                        }
                    }

                    if(!$error) {
                        \App\System::getInstance()->addMessage('success', 'Bilder wurden gespeichert!');
                    }
                }

                $this->view->setTemplate('product-update');
            }
            elseif($this->request->getParam('sub') == 'rent') {
                if(!$product->isAvailable()) {
                    $action = current(\App\Models\Action::grabByFilter(array(
                        array('product_id', '=', $product->getId()),
                        array('returnDate', 'IS', 'NULL')
                    )));

                    $this->view->assign('action', $action);
                    $this->view->setTemplate('product-rented');
                }
                else {
                    if($this->request->issetParam('submit')) {
                        try {
                            $customer = \App\Models\Customer::grab($this->request->getParam('internal_id'), 'internal_id');
                        }
                        catch(\App\Exceptions\NothingFoundException $e) {
                            $customer = \App\Models\Customer::new();
                            $customer->set('internal_id', $this->request->getParam('internal_id'));
                        }

                        $expectedReturnDate = !empty($this->request->getParam('expectedReturnDate')) ? $this->request->getParam('expectedReturnDate') : null;

                        $action = \App\Models\Action::new();
                        $action->set('product', $product);
                        $action->set('customer', $customer);
                        $action->set('rentDate', 'NOW()');
                        $action->set('expectedReturnDate', $expectedReturnDate);

                        if($action->save()) {
                            \App\System::getInstance()->addMessage('success', 'Produkt verliehen!');
                        }
                        else {
                            \App\System::getInstance()->addMessage('error', 'Produkt konnte nicht verliehen werden!');
                        }
                    }

                    $this->view->setTemplate('product-rent');
                }
            }
            elseif($this->request->getParam('sub') == 'return') {
                try {
                    $action = \App\Models\Action::grabByFilter(array(
                        array('returnDate' , 'IS', 'NULL'),
                        array('product_id', '=', $product->getId())
                    ));

                    if(count($action) == 1) {
                        $action = current($action);
                    }
                    elseif(count($action) == 0) {
                        \App\System::getInstance()->addMessage('error', 'Produkt wurde bereits zurückgegeben!');
                    }
                    else {
                        \App\Debugger::log("Es gibt mehrere aktive Aktionen für das Produkt {$product->getId()}! DAS SOLLTE NICHT PASSIEREN!", 'fatal');
                        \App\System::getInstance()->addMessage('error', 'Fehler beim Zurückgeben!');
                    }

                    if($this->request->issetParam('submit')) {
                        if($product->isAvailable()) {
                            \App\System::getInstance()->addMessage('error', 'Produkt wurde bereits zurückgegeben!');
                        }
                        else {
                            if($this->request->issetParam('returnDate')) {
                                $date = \DateTime::createFromFormat('d.m.Y', $this->request->getParam('returnDate'));
                                if($date !== false) {
                                    $action->returnProduct($date->format('Y-m-d H:i:s'));
                                }
                                else {
                                    $action->returnProduct();
                                }
                            }
                            else $action->returnProduct();

                            \App\System::getInstance()->addMessage('success', "{$product->get('name')} wurde erfolgreich zurückgegeben!");
                        }
                    }
                }
                catch(\Exception $e) {
                    \App\System::getInstance()->addMessage('error', 'Produkt wurde bereits zurückgegeben!');
                }

                $this->view->setTemplate('product-return');
            }
            elseif($this->request->getParam('sub') == 'claim') {

            }
        }
        else {
            try {
                $this->view->assign('rentHistory', \App\Models\Action::grabByFilter(array(
                    array('product_id', '=', $this->request->getParam('id'))
                ), 10));
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                $this->view->assign('rentHistory', array());
            }

            $this->view->setTemplate('product');
        }
    }

    public function products() {
        if($this->request->getParam('sub') == 'add') {
            //\App\Debugger::log('hello there');
            if($this->request->issetParam('submit')) {
                $product = \App\Models\Product::new();

                $params = $this->request->getParams();

            if(empty($this->request->getParam('name')) /*|| empty($this->request->getParam('invNr'))*/) {
                    \App\System::getInstance()->addMessage('error', 'Name muss angegeben werden!');
                }
                else {
                    foreach($params as $key) {
                        $product->set($key, $this->request->getParam($key));
                    }

                    try {
                        $product->save();

                        if($this->request->issetFile("add-productImage")) {
                            $files = $this->request->getFiles("add-productImage");
                            $error = false;

                            foreach($files as $file) {
                                $image = new \App\File\Image($file);

                                if($image->isValid()) {
                                    if($image->save("/images")) {
                                        $product_image = \App\Models\ProductImage::new();
                                        $product_image->set('src', "/public/files/images/{$image->getDestination()}");
                                        $product_image->set('product', $product);
                                        $product_image->set('title', $image->getInfo('name'));
                                        if($product_image->save()) {
                                            $product->addImage($product_image);
                                        }
                                    }
                                    else {
                                        $error = true;
                                        \App\System::getInstance()->addMessage('error', "Fehler beim Speichern von {$image->getInfo('name')}");
                                    }
                                }
                                else {
                                    $error = true;
                                    if(get_class($image->getError()) !== 'App\File\NoFileSentException') {
                                        \App\System::getInstance()->addMessage('error', $image->getError()->getMessage());
                                    }
                                }
                            }

                            if(!$error) {
                                \App\System::getInstance()->addMessage('success', 'Bilder wurden gespeichert!');
                            }
                        }

                        \App\System::getInstance()->addMessage('success', $product->get('name'). ' wurde erstellt! <a href="/products/'. $product->getId() .'">zum Produkt</a>');
                    }
                    catch(\Exception $e) {
                        \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern!');
                    }
                }
            }

            $this->view->setTemplate('product-add');
        }
        elseif($this->request->getParam('sub') == 'search') {
            if($this->request->issetParam('search_string')) {
                $_SESSION['search_string'] = $this->request->getParam('search_string');
            }

            $search_string = $_SESSION['search_string'];
            $currentPage = $this->request->issetParam('paginatorPage') ? intval($this->request->getParam('paginatorPage')) : 1;
            $itemsPerPage = 10;

            try {
                $filter = array(
                    array(
                        array('name', 'LIKE', "%{$search_string}%"),
                        'OR',
                        array('type', 'LIKE', "%{$search_string}%")
                    ),
                    'OR',
                    array('invNr', 'LIKE', "%{$search_string}%")
                );

                $products = \App\Models\Product::grabByFilter($filter, (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage");

                $query = \App\Models\Product::getQuery($filter);

                $paginator = new \App\Paginator($query, $currentPage, $itemsPerPage);

                $this->view->assign('paginator', $paginator);
                $this->view->assign('totals', $paginator->getTotals());
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                $products = array();
                $this->view->assign('totals', 0);
                \App\System::getInstance()->addMessage('error', 'Keine Ergebnisse gefunden!');
            }

            $this->view->assign('products', $products);
            $this->view->assign('search_string', $search_string);

            $this->view->setTemplate('products-search');
        }
        elseif($this->request->getParam('sub') == 'rent') {
            if($this->request->issetParam('submit') && $this->request->issetParam('search')) {
                try {
                    $search_string = $this->request->getParam('search');
                    $filter = array(
                        array(
                            array('name', 'LIKE', "%{$search_string}%"),
                            'OR',
                            array('type', 'LIKE', "%{$search_string}%")
                        ),
                        'OR',
                        array('invNr', 'LIKE', "%{$search_string}%")
                    );

                    $products = \App\Models\Product::grabByFilter($filter, 8);
                    $products = array_filter($products, function($p) {
                        return $p->isAvailable();
                    });

                    if(count($products) == 0) {
                        \App\System::getInstance()->addMessage('error', 'Es wurde kein passendes Produkt gefunden!');
                    }
                    elseif(count($products) == 1) {
                        $this->response->setStatus(301);
                        $this->response->addHeader('Location', '/products/rent/'. current($products)->getId());
                        $this->response->flush();
                        return;
                    }
                    else {
                        \App\System::getInstance()->addMessage('info', 'Die Suche lieferte mehrere Ergebnisse.');
                        $this->view->assign('products', $products);
                        $this->view->assign('string', $search_string);

                        $rentButton = new \App\Button();
                        $rentButton->set('href', '/products/rent/__id__');
                        $rentButton->set('style', 'primary');
                        $rentButton->set('text', 'Leihen');

                        $this->view->assign('buttons', array($rentButton));
                    }
                    //\App\System::getInstance()->addMessage('success', $product->get('name'). ' wurde verliehen!');
                }
                catch(\App\Exceptions\NothingFoundException $e) {
                    \App\System::getInstance()->addMessage('error', 'Es wurde kein passendes Produkt gefunden!');
                }
            }

            $this->view->setTemplate('product-search-mask');
        }
        else {
            $currentPage = $this->request->issetParam('paginatorPage') ? intval($this->request->getParam('paginatorPage')) : 1;
            $itemsPerPage = 8;

            try {
                $products = \App\Models\Product::grabByFilter(array(), (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage");

                $query = \App\Models\Product::getQuery(array());
                $paginator = new \App\Paginator($query, $currentPage, $itemsPerPage);
                $this->view->assign('paginator', $paginator);
                $this->view->assign('totals', $paginator->getTotals());
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                $products = array();
                $this->view->assign('totals', 0);
                \App\System::getInstance()->addMessage('error', 'Keine Ergebnisse gefunden!');
            }

            $query = new \App\QueryBuilder\Builder('products', true);
            $query->select(\App\QueryBuilder\Builder::alias($query::raw('DISTINCT type'), 'name'));
            $query->where('deleted', '0');
            $this->view->assign('categories', $query->get());

            $this->view->assign('products', $products);
            $this->view->setTemplate('products');
            /*$this->view->assign('products', \App\Models\Product::grabAll());
            $this->view->setTemplate('products');*/
        }

        $this->renderContent();
    }

    public function home() {
        try {
            $this->view->assign('actions', \App\Models\Action::grabByFilter(array(
                array('returnDate', 'IS', 'NULL')
            )));
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $this->view->assign('actions', array());
        }

        $query = new \App\QueryBuilder\Builder('actions' );
        $query->select(\App\QueryBuilder\Builder::alias('COUNT(*)', 'count'));
        $query->select('product_id');
        $query->groupBy('product_id');

         $products = array();
        foreach($query->get() as $p_info) {
            try {
                $products[] = array(
                    'product' => \App\Models\Product::grab($p_info['product_id']),
                    'frequency' => $p_info['count']
                );
            }
            catch(\App\Exceptions\NothingFoundException $e) {}
        }

        $this->view->assign('productsArray', $products);
        $this->view->setTemplate('products-rented');
        $this->renderContent();
    }

    public function customers() {
        $this->view->assign('customers', \App\Models\Customer::grabAll());
        $this->view->setTemplate('customers');

        $this->renderContent();
    }

    public function error($status) {
        $this->response->setStatus($status);
        $this->view->setTemplate('error');

        $this->renderContent();
    }
}
?>