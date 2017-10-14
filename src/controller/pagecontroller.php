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
                    catch(\InvalidArgumentException $e) {

                    }

                    if($this->request->issetParam('submit')) {
                        try {
                            $customer = \App\Models\Customer::grab($this->request->getParam('internal_id'), 'internal_id');
                        }
                        catch(\InvalidArgumentException $e) {
                            $customer = \App\Models\Customer::new();
                            $customer->set('internal_id', $this->request->getParam('internal_id'));
                        }

                        $expectedReturnDate = !empty($this->request->getParam('expectedReturnDate')) ? $this->request->getParam('expectedReturnDate') : null;

                        $action = \App\Models\Action::new();
                        $action->set('product', $product);
                        $action->set('customer', $customer);
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

                }
                elseif($this->request->getParam('sub') == 'claim') {

                }
            }
            else {
                try {
                    $this->view->assign('rentHistory', \App\Models\Action::grabByFilter(array(array('product_id', '=', $this->request->getParam('id')))));
                }
                catch(\Exception $e) {
                    $this->view->assign('rentHistory', array());
                }

                $this->view->setTemplate('product');
            }
        }
        else {
            $this->view->assign('products', \App\Models\Product::grabAll());
            $this->view->setTemplate('products');
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