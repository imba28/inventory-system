<?php
namespace App\Controllers;

use App\File\NoFileSentException;
use App\Model;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Customer;
use App\Models\Action;
use App\QueryBuilder\Builder;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends ApplicationController
{
    public function init()
    {
        parent::init();
        $this->beforeAction(
            ['update', 'delete', 'show', 'edit', 'rent', 'return', 'request'],
            function ($params) {
                try {
                    $this->product = Product::find($params['id']);
                } catch (\App\Exceptions\NothingFoundException $e) {
                    $this->error(404);
                }
                $this->view->assign('product', $this->product);
            }
        );
        $this->beforeAction(
            ['new', 'update', 'delete', 'create'],
            function ($params) {
                $this->authenticateUser();
            }
        );
    }

    /*public function product($params)
    {
        if (is_null($this->product)) {
            $this->error(404, 'Produkt wurde nicht gefunden!');
        } else {
            $this->view->assign('product', $this->product);

            if (isset($params['action'])) {
                if ($params['action'] == 'edit') {
                    $this->edit();
                } elseif ($params['action'] == 'rent') {
                    $this->rent();
                } elseif ($params['action'] == 'return') {
                    $this->return();
                } elseif ($params['action'] == 'request') {
                    $this->request();
                } elseif ($params['action'] == 'claim') {
                    //TODO: implements claim product
                }
            } else {
                $this->show($this->product);
            }
        }
    }*/

    public function show()
    {
        $this->respondTo(
            function ($wants) {
                $wants->html(
                    function () {
                        $this->view->setTemplate('product');
                        try {
                            $this->view->assign(
                                'rentHistory',
                                Action::findByFilter(
                                    array(
                                    'product',
                                    '=',
                                    $this->product
                                    ),
                                    10
                                )
                            );
                        } catch (\App\Exceptions\NothingFoundException $e) {
                            $this->view->assign('rentHistory', array());
                        }
                    }
                );
            }
        );
    }

    public function new()
    {
        $this->product = Product::new();
        $this->product->set('user', $this->getCurrentUser());

        $this->view->assign('product', $this->request);

        $this->view->setTemplate('product-add');
    }

    public function create()
    {
        $this->new();
        $this->update();

        $this->view->setTemplate('product-add');
    }

    public function delete(array $params)
    {
        if ($this->product->remove() === false) {
            self::$status->add('errors', 'Es ist ein Fehler beim Löschen aufgetreten!');
            $this->redirectToRoute('/product/' . $this->product->getId());
        } else {
            self::$status->add('success', 'Produkt wurde gelöscht.');
            $this->redirectToRoute('/products');
        }
    }

    public function search($params)
    {
        if ($this->request->issetParam('search_string')) {
            $_SESSION['search_string'] = $this->request->getParam('search_string');
        }

        $searchString = $_SESSION['search_string'];
        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
        $itemsPerPage = 8;

        try {
            $filter = array(
                array(
                    array('name', 'LIKE', "%{$searchString}%"),
                    'OR',
                    array('type', 'LIKE', "%{$searchString}%")
                ),
                'OR',
                array('invNr', 'LIKE', "%{$searchString}%")
            );

            $products = Product::findByFilter(
                $filter,
                (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage"
            );

            $paginator = new \App\Paginator(
                Product::getQuery($filter)->count(),
                $currentPage,
                $itemsPerPage,
                '/products/search'
            );

            $this->view->assign('paginator', $paginator);
            $this->view->assign('totals', $paginator->getTotals());
        } catch (\App\Exceptions\NothingFoundException $e) {
            $products = array();
            $this->view->assign('totals', 0);
            self::$status->add('info', 'Deine Suche lieferte keine Ergebnisse!');
        }

        $this->view->assign('products', $products);
        $this->view->assign('search_string', $searchString);

        $this->view->setTemplate('products-search');
    }

    /*public function products($params = array())
    {
        if (isset($params['action'])) {
            if (intval($params['action']) > 0) { // TODO: naja, echt grausig....
                $this->products(
                    array(
                    'page' => $params['action']
                    )
                );
                return;
            } elseif ($params['action'] == 'add') {
                $this->create();
            } elseif ($params['action'] == 'rent') {
                $this->rentMask();
            }
        } else {
            $this->index($params);
            /*$this->view->assign('products', Product::all());
            $this->view->setTemplate('products');*/
            /*
        }
    }*/

    public function home()
    {
        if ($this->isUserSignedIn()) {
            try {
                $this->view->assign(
                    'actions',
                    Action::findByFilter(
                        array(
                        array('returnDate', 'IS', 'NULL')
                        )
                    )
                );
            } catch (\App\Exceptions\NothingFoundException $e) {
                $this->view->assign('actions', array());
            }

            $query = new Builder('actions');
            $query->select(Builder::alias('COUNT(*)', 'count'));
            $query->select('product_id');
            $query->groupBy('product_id');
            $query->orderBy(Builder::raw('count'));
            $query->orderBy('product_id');
            $query->limit(10);

            $products = array();
            foreach ($query->get() as $productData) {
                try {
                    $products[] = array(
                        'product' => Product::find($productData['product_id']),
                        'frequency' => $productData['count']
                    );
                } catch (\App\Exceptions\NothingFoundException $e) {
                }
            }

            $this->view->assign('topProducts', $products);
            $this->view->setTemplate('products-rented');
        } else {
            $this->redirectToRoute('/products');
        }
    }

    public function displayCategories()
    {
        $categories = $this->getCategories();
        $this->view->assign('categories', $categories);
    }

    public function displayCategory($params)
    {
        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
        $itemsPerPage = 8;

        try {
            $products = Product::findByFilter(
                array(
                'type', '=', $params['category']
                ),
                (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage"
            );

            $paginator = new \App\Paginator(
                Product::getQuery(
                    array('type', '=', $params['category'])
                )
                ->count(),
                $currentPage,
                $itemsPerPage,
                '/products/category/' . urlencode($params['category'])
            );

            $this->view->assign('paginator', $paginator);
            $this->view->assign('totals', $paginator->getTotals());
        } catch (\App\Exceptions\NothingFoundException $e) {
            $products = array();
            $this->view->assign('totals', 0);
            self::$status->add('info', 'Deine Suche lieferte keine Ergebnisse!');
        }

        $query = new Builder('products');
        $query->select(Builder::alias($query::raw('DISTINCT type'), 'name'));
        $query->where('deleted', '0');
        $this->view->assign('categories', $query->get());

        $this->view->assign('products', $products);
        $this->view->setTemplate('products');
    }

    public function error($status, $message = 'Produkt wurde nicht gefunden!')
    {
        $this->response->setStatus($status);
        $this->view->setTemplate('error');
        $this->view->assign('errorCode', $status);
        $this->view->assign('errorMessage', $message);

        $this->renderContent();
        exit();
    }

    // INTERNAL METHODS

    public function edit()
    {
        $this->view->setTemplate('product-update');
    }

    public function update()
    {
        if ($this->saveUploadedImages()) {
            $this->product->setAll($this->request->getParams());

            try {
                $this->product->save();
                self::$status->add('success', $this->product->get('name'). ' wurde aktualisiert!');
            } catch (\App\QueryBuilder\QueryBuilderException $e) {
                list($errorCode, $errorMessage, $errorValue, $errorColumn) = $e->getData();
                self::$status->add('errors', $errorMessage);
            } catch (\InvalidOperationException $e) {
                self::$status->add('errors', 'Fehler beim Speichern! ' . $e->getMessage());
            } catch (\App\Models\InvalidModelDataException $e) {
                foreach ($this->product->messages()->get('errors') as $error) {
                    self::$status->add('errors', $error);
                }
            } catch (\Exception $e) {
                self::$status->add('errors', 'Fehler beim Speichern!');
            }

            $this->edit();
        }
    }

    public function rent()
    {
        if (!$this->product->isAvailable()) {
            $action = Action::findByFilter(
                array(
                    array('product_id', '=', $this->product->getId()),
                    array('returnDate', 'IS', 'NULL')
                ),
                1
            );

            $this->view->assign('action', $action);
            $this->view->setTemplate('product-rented');
        } else {
            $this->view->setTemplate('product-rent');
            
            if ($this->request->issetParam('submit')) {
                $customer = Customer::findOrCreate($this->request->getParam('internal_id'), 'internal_id');
                if (!$customer->isCreated()) {
                    try {
                        if ($customer->save()) {
                            self::$status->add(
                                'info',
                                "Ein neuer Kunde
                                <a href='/customer/{$customer->getId()}/edit'>
                                    {$customer->get('internal_id')}
                                </a>
                                wurde angelegt."
                            );
                        }
                    } catch (\App\Models\InvalidModelDataException $e) {
                        foreach ($customer->messages()->get('errors') as $error) {
                            self::$status->add('errors', $error);
                        }
                        return;
                    }
                }

                try {
                    if ($this->product->rent(
                        $customer,
                        $this->request->getParam('expectedReturnDate'),
                        $this->getCurrentUser()
                    )) {
                        self::$status->add('success', 'Produkt verliehen!');
                    } else {
                        self::$status->add('errors', 'Produkt konnte nicht verliehen werden!');
                    }
                } catch (\App\Models\InvalidModelDataException $e) {
                    self::$status->add('errors', join(', ', $action->getErrors()));
                }
            }
        }
    }

    public function return()
    {
        $this->view->setTemplate('product-return');

        if ($this->product->isAvailable()) {
            self::$status->add('errors', 'Produkt wurde bereits zurückgegeben!');
            return;
        }

        if ($this->request->issetParam('submit')) {
            $returnDate = 'NOW';

            if ($this->request->issetParam('returnDate')) {
                $date = \DateTime::createFromFormat('d.m.Y', $this->request->getParam('returnDate'));
                if ($date !== false) {
                    $returnDate = $date->format('Y-m-d H:i:s');
                }
            }

            $this->product->getRentalAction()->returnProduct($returnDate);

            self::$status->add('success', "{$this->product->get('name')} wurde erfolgreich zurückgegeben!");
        }
    }

    public function rentMask()
    {
        $this->authenticateUser();

        if ($this->request->issetParam('submit') && $this->request->issetParam('search')) {
            try {
                $searchString = $this->request->getParam('search');

                $this->view->assign('search_string', $searchString);
                $this->view->assign('totals', 0);

                $filter = array(
                    array(
                        array('name', 'LIKE', "%{$searchString}%"),
                        'OR',
                        array('type', 'LIKE', "%{$searchString}%")
                    ),
                    'OR',
                    array('invNr', 'LIKE', "%{$searchString}%")
                );

                $products = Product::findByFilter($filter);
                $products = array_filter(
                    $products->toArray(),
                    function ($p) {
                        return $p->isAvailable();
                    }
                );

                if (count($products) == 0) {
                    self::$status->add('errors', 'Es wurde kein passendes Produkt gefunden!');
                } elseif (count($products) == 1) {
                    $this->response->redirect('/product/'. current($products)->getId() . '/rent');
                } else {
                    self::$status->add('info', 'Die Suche lieferte mehrere Ergebnisse.');
                    $this->view->assign('products', $products);
                    $this->view->assign('totals', count($products));

                    $rentButton = new \App\Button();
                    $rentButton->set('href', '/product/__id__/rent');
                    $rentButton->set('style', 'primary');
                    $rentButton->set('text', 'Verleihen');

                    $this->view->assign('buttons', array($rentButton));
                }
                //self::$status->add('success', $product->get('name'). ' wurde verliehen!');
            } catch (\App\Exceptions\NothingFoundException $e) {
                self::$status->add('errors', 'Es wurde kein passendes Produkt gefunden!');
            }
        }

        $this->view->setTemplate('products-search-mask');
    }

    public function index(Request $request)
    {
        $params = $request->request->all();
        vd($params);

        $this->view->setTemplate('products');

        $this->respondTo(
            function ($wants) use ($params) {
                $wants->html(
                    function () use ($params) {
                        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
                        $itemsPerPage = 8;

                        try {
                            $products = Product::findByFilter(
                                array(),
                                (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage"
                            );
                            $paginator = new \App\Paginator(
                                Product::getQuery(array())->count(),
                                $currentPage,
                                $itemsPerPage,
                                '/products'
                            );

                            $this->view->assign('products', $products);
                            $this->view->assign('categories', $this->getCategories());
                            $this->view->assign('totals', $paginator->getTotals());
                            $this->view->assign('paginator', $paginator);
                        } catch (\App\Exceptions\NothingFoundException $e) {
                            $this->view->assign('totals', 0);
                            self::$status->add('errors', 'Keine Ergebnisse gefunden!');
                        }
                    }
                );

                $wants->json(
                    function () {
                        $this->view->assign('products', Product::all());
                    }
                );
            }
        );
    }

    private function getCategories()
    {
        $query = new Builder('products');
        $query->select(Builder::alias($query::raw('DISTINCT type'), 'name'));
        $query->where('deleted', '0');

        return $query->get();
    }

    public function request()
    {
        if ($this->product->isAvailable()) {
            $adminEmail = \App\Configuration::get('admin_email');
            if (!is_array($adminEmail)) {
                $adminEmail = array($adminEmail);
            }

            $this->view->assign('admin_email', $adminEmail);
            $this->view->setTemplate('product-request');

            if ($this->request->issetParam('submit')) {
                if (empty($this->request->get('email'))) {
                    // honeypot für bots o.Ä. echte email adresse ist in 'aklnslknat'
                    if (empty($this->request->get('aklnslknat'))) {
                        self::$status->add('errors', 'Bitte gib deine E-Mail Adresse an!');
                    } else {
                        $to = implode($adminEmail, ';');
                        $subject = "MMT Verleih: Anfrage für Produkt {$this->product->get('name')}";
                        $message = "Anfrage für Verleih von {$this->product->get('name')} von {$this->request->get('name')}.";
                        $header = 'From: webmaster@example.com' . "\r\n" .
                            'Reply-To: '. $this->request->get('aklnslknat') . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                        if (mail($to, $subject, $message, $header)) {
                            self::$status->add('success', 'Deine Anfrage wurde erfolgreich gesendet!');
                        } else {
                            self::$status->add('errors', 'Deine Anfrage konnte leider nicht gesendet werden!');
                        }
                    }
                }
            }
        } else {
            self::$status->add('errors', 'Produkt ist bereits verliehen!');
            $this->redirectToRoute("/product/{$this->product->getId()}");
        }
    }

    private function saveUploadedImages()
    {
        if ($this->request->issetFile("add-productImage")) {
            $files = $this->request->getFiles("add-productImage");
            $success = true;

            foreach ($files as $file) {
                $image = new \App\File\Image($file);

                if ($image->isValid()) {
                    if ($image->save("/images")) {
                        $productImage = ProductImage::new();
                        $productImage->set('src', "/public/files/images/{$image->getDestination()}");
                        $productImage->set('product', $this->product);
                        $productImage->set('title', $image->getInfo('name'));
                        $productImage->set('user', $this->getCurrentUser());

                        $this->product->images()->append($productImage);
                    } else {
                        $success = false;
                        self::$status->add('errors', "Fehler beim Speichern von {$image->getInfo('name')}");
                    }
                } else {
                    $success = false;
                    if (get_class($image->getError()) !== 'App\File\NoFileSentException') {
                        vd($image->getError());
                        self::$status->add('errors', $image->getError()->getMessage());
                    } else {
                        return true;
                    }
                }
            }
            if ($success) {
                self::$status->add('success', 'Bilder wurden gespeichert!');
            }

            return $success;
        }
        
        return true;
    }
}
