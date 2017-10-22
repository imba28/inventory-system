<?php
namespace App\Controller;

class PageController implements \App\Interfaces\Controller {
    protected $layout;
    protected $response;
    protected $view;

    public function __construct($layout = 'default') {
        $this->layout = $layout;
        $this->response = new \App\HttpResponse();
        $this->request = new \App\HttpRequest();
        $this->view = new \App\View();

        $this->view->assign('request', $this->request);
    }

    public function products() {
        if($this->request->issetParam('id')) {
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
                            \App\System::getInstance()->addMessage('info', 'Es wurde nichts geändert.');
                        }
                        catch(\Exception $e) {
                            \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern!');
                        }
                    }
                    $this->view->setTemplate('product-update');
                }
                elseif($this->request->getParam('sub') == 'rent') {
                    try { // Check if already rented
                        $action = \App\Models\Action::grabByFilter(array(
                            array('product_id', '=', $product->get('id')),
                            array('returnDate', 'IS', 'NULL')
                        ));
                        if(count($action) !== 0) {
                            $this->view->setTemplate('product-rented');
                            $this->view->assign('action', current($action));
                            $this->renderContent();
                            return;
                        }
                    }
                    catch(\App\Exceptions\NothingFoundException $e) {}

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
                    $this->view->assign('rentHistory', \App\Models\Action::grabByFilter(array(array('product_id', '=', $this->request->getParam('id')))));
                }
                catch(\App\Exceptions\NothingFoundException $e) {
                    $this->view->assign('rentHistory', array());
                }

                $this->view->setTemplate('product');
            }
        }
        elseif($this->request->getParam('sub') == 'add') {
            //\App\Debugger::log('hello there');
            if($this->request->issetParam('submit')) {
                $product = \App\Models\Product::new();

                $params = $this->request->getParams();

                if(empty($this->request->getParam('name')) || empty($this->request->getParam('invNr'))) {
                    \App\System::getInstance()->addMessage('error', 'Name/Inventarnummer muss angegeben werden!');
                }
                else {
                    foreach($params as $key) {
                        $product->set($key, $this->request->getParam($key));
                    }

                    try {
                        $product->save();
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
            $itemsPerPage = 2;
            try {
                $filter = array(
                    array(
                        array('name', 'LIKE', "%{$search_string}%"),
                        'OR',
                        array('type', 'LIKE', "%{$search_string}%")
                    )
                );

                $products = \App\Models\Product::grabByFilter($filter, (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage");

                $query = \App\Models\Product::getQuery($filter);
                $paginator = new \App\Paginator($query, $currentPage, $itemsPerPage);
                $this->view->assign('paginator', $paginator);
                $this->view->assign('totals', $paginator->getTotals());
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                $products = array();
                \App\System::getInstance()->addMessage('error', 'Keine Ergebnisse gefunden!');
            }

            $this->view->assign('products', $products);
            $this->view->assign('search_string', $search_string);

            $this->view->setTemplate('products-search');
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
                \App\System::getInstance()->addMessage('error', 'Keine Ergebnisse gefunden!');
            }

            $this->view->assign('products', $products);
            $this->view->setTemplate('products');
            /*$this->view->assign('products', \App\Models\Product::grabAll());
            $this->view->setTemplate('products');*/
        }

        $this->renderContent();
    }

    public function home() {
        $filters = array(
            array('returnDate', 'IS', 'NULL')
        );

        $this->view->assign('actions', \App\Models\Action::grabByFilter($filters));
        $this->view->setTemplate('home');

        $this->renderContent();
    }

    public function customers() {
        $this->view->assign('customers', \App\Models\Customer::grabAll());
        $this->view->setTemplate('customers');

        $this->renderContent();
    }

    public function error($status) {
        $this->response->setSatus($status);
        $this->view->setTemplate('error');

        $this->renderContent();
    }


    private function renderContent() {
        $this->response->append($this->getLayoutComponent('head'));
        $this->response->append($this->view->render());
        $this->response->append($this->getLayoutComponent('footer'));
        $this->response->flush();
    }

    private function bufferContent($path) {
        ob_start();
        include($path);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
    private function getLayoutComponent($type = 'head') {
        if(file_exists(ABS_PATH."/src/layouts/{$this->layout}-{$type}.php")) {
            return $this->bufferContent(ABS_PATH."/src/layouts/{$this->layout}-{$type}.php");
        }
        throw new \InvalidArgumentException("Layout {$this->layout}-{$type}` does not exists!`");
    }
}
?>