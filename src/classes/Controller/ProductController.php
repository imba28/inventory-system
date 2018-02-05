<?php
namespace App\Controller;

class ProductController extends ApplicationController {
    public function __construct($responseType = 'html', $layout = 'default') {
        parent::__construct($responseType, $layout);

        $this->beforeAction('product', function($params) {
            try {
                $this->product = \App\Models\Product::find($params['id']);
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                $this->product = null;
            }
        });
    }

    public function product($params) {
        if(is_null($this->product)) $this->error(404, 'Produkt wurde nicht gefunden!');
        else {
            $this->view->assign('product', $this->product);

            if(isset($params['action'])) {
                if($params['action'] == 'edit') {
                    $this->edit();
                }
                elseif($params['action'] == 'rent') {
                    $this->rent();
                }
                elseif($params['action'] == 'return') {
                    $this->return();
                }
                elseif($params['action'] == 'request') {
                    $this->request();
                }
                elseif($params['action'] == 'claim') {
                    //TODO: implements claim product
                }
            }
            else {
                if($this->responseType === 'html') $this->display($this->product);
                else {
                    $this->renderContent();
                }
            }
        }
    }

    public function delete(array $params) {
        $this->authenticateUser();

        if(isset($params['id'])) {
            if(\App\Models\Product::delete(intval($params['id'])) === false) {
                \App\System::getInstance()->addMessage('error', 'Es ist ein Fehler beim Löschen aufgetreten!');
            }
            else {
                \App\System::getInstance()->addMessage('success', 'Produkt wurde gelöscht.');
            }

            $this->products(array());
        }
    }

    public function search($params) {
        if($this->request->issetParam('search_string')) {
            $_SESSION['search_string'] = $this->request->getParam('search_string');
        }

        $search_string = $_SESSION['search_string'];
        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
        $itemsPerPage = 8;

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

            $products = \App\Models\Product::findByFilter($filter, (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage");

            $query = \App\Models\Product::getQuery($filter);

            $paginator = new \App\Paginator($query, $currentPage, $itemsPerPage, '/products/search');

            $this->view->assign('paginator', $paginator);
            $this->view->assign('totals', $paginator->getTotals());
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $products = array();
            $this->view->assign('totals', 0);
            \App\System::getInstance()->addMessage('info', 'Deine Suche lieferte keine Ergebnisse!');
        }

        $this->view->assign('products', $products);
        $this->view->assign('search_string', $search_string);

        $this->view->setTemplate('products-search');
    }

    public function products($params) {
        if(isset($params['action'])) {
            if(intval($params['action']) > 0) { // TODO: naja, echt grausig....
                $this->products(array(
                    'page' => $params['action']
                ));
                return;
            }
            elseif($params['action'] == 'add') {
                $this->addProduct();
            }
            elseif($params['action'] == 'rent') {
                $this->rentMask();
            }
        }
        else {
            $this->displayProducts($params);
            /*$this->view->assign('products', \App\Models\Product::all());
            $this->view->setTemplate('products');*/
        }
    }

    public function home() {
        if($this->isUserSignedIn()) {
            try {
                $this->view->assign('actions', \App\Models\Action::findByFilter(array(
                    array('returnDate', 'IS', 'NULL')
                )));
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                $this->view->assign('actions', array());
            }

            $query = new \App\QueryBuilder\Builder('actions');
            $query->select(\App\QueryBuilder\Builder::alias('COUNT(*)', 'count'));
            $query->select('product_id');
            $query->groupBy('product_id');
            $query->orderBy(\App\QueryBuilder\Builder::raw('count'));
            $query->orderBy('product_id');

            $products = array();
            foreach($query->get() as $p_info) {
                try {
                    $products[] = array(
                        'product' => \App\Models\Product::find($p_info['product_id']),
                        'frequency' => $p_info['count']
                    );
                }
                catch(\App\Exceptions\NothingFoundException $e) {}
            }

            $this->view->assign('topProducts', $products);
            $this->view->setTemplate('products-rented');
        }
        else {
            $this->redirectToRoute('/products');
        }
    }

    public function displayCategories($params) {
        $categories = $this->getCategories();
        $this->view->assign('categories', $categories);
    }

    public function displayCategory($params) {
        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
        $itemsPerPage = 8;

        try {
            $products = \App\Models\Product::findByFilter(array(
                'type', '=', $params['category']
            ), (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage");

            $query = \App\Models\Product::getQuery(array('type', '=', $params['category']));
            $paginator = new \App\Paginator($query, $currentPage, $itemsPerPage, '/products/category/' . urlencode($params['category']));
            $this->view->assign('paginator', $paginator);
            $this->view->assign('totals', $paginator->getTotals());
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $products = array();
            $this->view->assign('totals', 0);
            \App\System::getInstance()->addMessage('info', 'Deine Suche lieferte keine Ergebnisse!');
        }

        $query = new \App\QueryBuilder\Builder('products');
        $query->select(\App\QueryBuilder\Builder::alias($query::raw('DISTINCT type'), 'name'));
        $query->where('deleted', '0');
        $this->view->assign('categories', $query->get());

        $this->view->assign('products', $products);
        $this->view->setTemplate('products');
    }

    public function error($status, $message = '') {
        $this->response->setStatus($status);
        $this->view->setTemplate('error');
        $this->view->assign('errorCode', $status);
        $this->view->assign('errorMessage', $message);
    }

    // INTERNAL METHODS

    private function edit() {
        $this->authenticateUser();

        $this->view->setTemplate('product-update');

        if($this->request->issetParam('submit')) {
            $params = $this->request->getParams();
            foreach($params as $key) {
                $this->product->set($key, $this->request->getParam($key));
            }

            if(empty($this->product->get('name')) || empty($this->product->get('invNr'))) {
                \App\System::getInstance()->addMessage('error', 'Name/Inventar Nummer muss angegeben werden!');
                return;
            }

            try {
                $this->product->save();
                \App\System::getInstance()->addMessage('success', $this->product->get('name'). ' wurde aktualisiert!');
            }
            catch(\App\QueryBuilder\QueryBuilderException $e) {
                list($error_code, $error_message, $error_value, $error_column) = $e->getData();
                \App\System::getInstance()->addMessage('error', $error_message);
            }
            catch(\App\QueryBuilder\NothingChangedException $e) {
                //\App\System::getInstance()->addMessage('info', 'Es wurde nichts geändert.');
            }
            catch(\InvalidOperationException $e) {
                \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern! ' . $e->getMessage());
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
                        $product_image->set('product', $this->product);
                        $product_image->set('title', $image->getInfo('name'));
                        $product_image->set('user', $this->getCurrentUser());

                        if($product_image->save()) {
                            $this->product->addImage($product_image);
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
    }

    private function rent() {
        $this->authenticateUser();

        if(!$this->product->isAvailable()) {
            $action = current(\App\Models\Action::findByFilter(array(
                array('product_id', '=', $this->product->getId()),
                array('returnDate', 'IS', 'NULL')
            )));

            $this->view->assign('action', $action);
            $this->view->setTemplate('product-rented');
        }
        else {
            if($this->request->issetParam('submit')) {
                try {
                    $customer = \App\Models\Customer::find($this->request->getParam('internal_id'), 'internal_id');
                }
                catch(\App\Exceptions\NothingFoundException $e) {
                    $customer = \App\Models\Customer::new();
                    $customer->set('user', $this->getCurrentUser());
                    $customer->set('internal_id', $this->request->getParam('internal_id'));

                    $customer->save();

                    \App\System::getInstance()->addMessage('info', "Ein neuer Kunde <a href='/customer/{$customer->getId()}/edit'> {$customer->get('internal_id')}</a> wurde angelegt.");
                }

                $expectedReturnDate = !empty($this->request->getParam('expectedReturnDate')) ? $this->request->getParam('expectedReturnDate') : null;

                $action = \App\Models\Action::new();
                $action->set('product', $this->product);
                $action->set('customer', $customer);
                $action->set('rentDate', 'NOW()');
                $action->set('expectedReturnDate', $expectedReturnDate);
                $action->set('user', $this->getCurrentUser());

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

    private function return() {
        $this->authenticateUser();

        try {
            $action = \App\Models\Action::findByFilter(array(
                array('returnDate' , 'IS', 'NULL'),
                array('product_id', '=', $this->product->getId())
            ), 1);

            if(count($action) == 0) {
                \App\System::getInstance()->addMessage('error', 'Produkt wurde bereits zurückgegeben!');
            }
            elseif(count($action) > 1) {
                \App\Debugger::log("Es gibt mehrere aktive Aktionen für das Produkt {$this->product->getId()}! DAS SOLLTE NICHT PASSIEREN!", 'fatal');
                \App\System::getInstance()->addMessage('error', 'Das Produkt wurde mehrfach verliehen? Administrator kontaktieren.');
            }
            else {
                if($this->request->issetParam('submit')) {
                    if($this->product->isAvailable()) {
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

                        \App\System::getInstance()->addMessage('success', "{$this->product->get('name')} wurde erfolgreich zurückgegeben!");
                    }
                }
            }
        }
        catch(\Exception $e) {
            \App\System::getInstance()->addMessage('error', 'Produkt wurde bereits zurückgegeben!');
        }

        $this->view->setTemplate('product-return');
    }

    private function display() {
        try {
            $this->view->assign('rentHistory', \App\Models\Action::findByFilter(array('product', '=', $this->product), 10));
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $this->view->assign('rentHistory', array());
        }

        $this->view->setTemplate('product');
    }

    private function addProduct() {
        $this->authenticateUser();

        //\App\Debugger::log('hello there');
        if($this->request->issetParam('submit')) {
            $this->product = \App\Models\Product::new();
            $this->product->set('user', $this->getCurrentUser());

            $params = $this->request->getParams();

            if(empty($this->request->getParam('name')) || empty($this->request->getParam('invNr'))) {
                \App\System::getInstance()->addMessage('error', 'Name/Inventar Nummer muss angegeben werden!');
            }
            else {
                foreach($params as $key) {
                    $this->product->set($key, $this->request->getParam($key));
                }

                try {
                    $this->product->save();

                    if($this->request->issetFile("add-productImage")) {
                        $files = $this->request->getFiles("add-productImage");
                        $error = false;

                        foreach($files as $file) {
                            $image = new \App\File\Image($file);

                            if($image->isValid()) {
                                if($image->save("/images")) {
                                    $product_image = \App\Models\ProductImage::new();
                                    $product_image->set('src', "/public/files/images/{$image->getDestination()}");
                                    $product_image->set('product', $this->product);
                                    $product_image->set('title', $image->getInfo('name'));
                                    $product_image->set('user', $this->getCurrentUser());

                                    if($product_image->save()) {
                                        $this->product->addImage($product_image);
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

                    \App\System::getInstance()->addMessage('success', $this->product->get('name'). ' wurde erstellt! <a href="/product/'. $this->product->getId() .'">zum Produkt</a>');
                }
                catch(\App\QueryBuilder\QueryBuilderException $e) {
                    list($error_code, $error_message, $error_value, $error_column) = $e->getData();
                    \App\System::getInstance()->addMessage('error', $error_message);
                }
                catch(\Exception $e) {
                    \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern!');
                }
            }
        }

        $this->view->setTemplate('product-add');
    }

    private function rentMask() {
        $this->authenticateUser();

        if($this->request->issetParam('submit') && $this->request->issetParam('search')) {
            try {
                $search_string = $this->request->getParam('search');

                $this->view->assign('search_string', $search_string);
                $this->view->assign('totals', 0);

                $filter = array(
                    array(
                        array('name', 'LIKE', "%{$search_string}%"),
                        'OR',
                        array('type', 'LIKE', "%{$search_string}%")
                    ),
                    'OR',
                    array('invNr', 'LIKE', "%{$search_string}%")
                );

                $products = \App\Models\Product::findByFilter($filter);
                $products = array_filter($products->toArray(), function($p) {
                    return $p->isAvailable();
                });

                if(count($products) == 0) {
                    \App\System::getInstance()->addMessage('error', 'Es wurde kein passendes Produkt gefunden!');
                }
                elseif(count($products) == 1) {
                    $this->response->redirect('/product/'. current($products)->getId() . '/rent');
                }
                else {
                    \App\System::getInstance()->addMessage('info', 'Die Suche lieferte mehrere Ergebnisse.');
                    $this->view->assign('products', $products);
                    $this->view->assign('totals', count($products));

                    $rentButton = new \App\Button();
                    $rentButton->set('href', '/product/__id__/rent');
                    $rentButton->set('style', 'primary');
                    $rentButton->set('text', 'Verleihen');

                    $this->view->assign('buttons', array($rentButton));
                }
                //\App\System::getInstance()->addMessage('success', $product->get('name'). ' wurde verliehen!');
            }
            catch(\App\Exceptions\NothingFoundException $e) {
                \App\System::getInstance()->addMessage('error', 'Es wurde kein passendes Produkt gefunden!');
            }
        }

        $this->view->setTemplate('products-search-mask');
    }

    private function displayProducts($params) {
        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
        $itemsPerPage = 8;

        try {
            $products = \App\Models\Product::findByFilter(array(), (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage");

            $query = \App\Models\Product::getQuery(array());
            $paginator = new \App\Paginator($query, $currentPage, $itemsPerPage, '/products');
            $this->view->assign('paginator', $paginator);
            $this->view->assign('totals', $paginator->getTotals());
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            $products = array();
            $this->view->assign('totals', 0);
            \App\System::getInstance()->addMessage('error', 'Keine Ergebnisse gefunden!');
        }

        $this->view->assign('categories', $this->getCategories());
        $this->view->assign('products', $products);

        $this->view->setTemplate('products');
    }

    private function getCategories() {
        $query = new \App\QueryBuilder\Builder('products');
        $query->select(\App\QueryBuilder\Builder::alias($query::raw('DISTINCT type'), 'name'));
        $query->where('deleted', '0');

        return $query->get();
    }

    private function request() {
        if($this->product->isAvailable()) {
            $adminEmail = \App\Configuration::get('admin_email');
            if(!is_array($adminEmail)) $adminEmail = array($adminEmail);

            $this->view->assign('admin_email', $adminEmail);
            $this->view->setTemplate('product-request');

            if($this->request->issetParam('submit')) {
                if(empty($this->request->get('email'))) {
                    // honeypot für bots o.Ä. echte email adresse ist in 'aklnslknat'
                    if(empty($this->request->get('aklnslknat'))) {
                        \App\System::getInstance()->addMessage('error', 'Bitte gib deine E-Mail Adresse an!');
                    }
                    else {
                        $to = implode($adminEmail, ';');
                        $subject = "MMT Verleih: Anfrage für Produkt {$this->product->get('name')}";
                        $message = "Anfrage für Verleih von {$this->product->get('name')} von {$this->request->get('name')}.";
                        $header = 'From: webmaster@example.com' . "\r\n" .
                            'Reply-To: '. $this->request->get('aklnslknat') . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                        if(mail($to, $subject, $message, $header)) {
                            \App\System::getInstance()->addMessage('success', 'Deine Anfrage wurde erfolgreich gesendet!');
                        }
                        else {
                            \App\System::getInstance()->addMessage('error', 'Deine Anfrage konnte leider nicht gesendet werden!');
                        }
                    }
                }
            }
        }
        else {
            \App\System::getInstance()->addMessage('error', 'Produkt ist bereits verliehen!');
            $this->redirectToRoute("/product/{$this->product->getId()}");
        }
    }
}
?>