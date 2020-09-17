<?php
namespace App\Controllers;

use App\Models\Customer;
use App\Models\Action;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends ApplicationController
{
    private $customer;

    public function init()
    {
        parent::init();
        
        $this->authenticateUser();

        $this->beforeAction(
            array('show', 'update', 'delete', 'edit'),
            function ($params) {
                try {
                    $this->customer = Customer::find($params['id']);
                    $this->view->assign('customer', $this->customer);
                } catch (\App\Exceptions\NothingFoundException $e) {
                    $this->error(404);
                }
            }
        );
    }

    public function index($params = array())
    {
        $this->respondTo(
            function ($wants) use ($params) {
                $wants->html(
                    function () use ($params) {
                        $currentPage = isset($params['page']) ? intval($params['page']) : 1;
                        $itemsPerPage = 8;

                        try {
                            $customers = Customer::findByFilter(
                                array(),
                                (($currentPage - 1) * $itemsPerPage ) . ", $itemsPerPage"
                            );
                            $paginator = new \App\Paginator(
                                Customer::getQuery(array())->count(),
                                $currentPage,
                                $itemsPerPage,
                                '/customers'
                            );

                            $this->view->assign('customers', $customers);
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
                        $this->view->assign('customers', Customer::all());
                    }
                );

                $wants->xml(
                    function () {
                        $this->view->assign('customers', Customer::all());
                    }
                );
            }
        );
    }

    public function show(array $params)
    {
        $this->respondTo(
            function ($wants) {
                $wants->html(
                    function () {
                        try {
                            /*$rentalHistory = Action::findByFilter(
                                array(
                                    array('customer', '=', $this->customer)
                                ),
                                10,
                                array('returnDate' => 'ASC')
                            );*/
                            $rentalHistory = Action::where('customer', $this->customer)->sort('rentDate', 'DESC')->first(10);
                        } catch (\App\Exceptions\NothingFoundException $e) {
                            $rentalHistory = array();
                        }

                        $this->view->assign('rentHistory', $rentalHistory);
                    }
                );
            }
        );
    }

    public function update(Request $request)
    {
        $this->customer->setAll($request->request->all());
 
        try {
            $this->customer->save();
            self::$status->add(
                'success',
                "<a href='/customer/{$this->customer->getId()}'>
                    {$this->customer->get('name')}
                </a>
                wurde gespeichert!"
            );
        } catch (\App\QueryBuilder\QueryBuilderException $e) {
            list($error_code, $error_message, $error_value, $error_column) = $e->getData();

            self::$status->add('errors', $error_message);
        } catch (\App\QueryBuilder\NothingChangedException $e) {
            //System::info('Es wurde nichts geändert.');
        } catch (\InvalidOperationException $e) {
            self::$status->add('errors', 'Fehler beim Speichern! ' . $e->getMessage());
        } catch (\App\Models\InvalidModelDataException $e) {
            foreach ($this->customer->messages()->get('errors') as $error) {
                self::$status->add('errors', $error);
            }
        } catch (\Exception $e) {
            self::$status->add('errors', 'Fehler beim Speichern!');
        }

        $this->edit(); // show edit form
    }

    public function edit()
    {
        $this->view->setTemplate('customer/edit');
    }

    public function create()
    {
        $this->new();
        $this->update();

        $this->view->setTemplate('customer/new');
    }

    public function delete()
    {
        if ($this->customer->remove()) {
            self::$status->add('success', 'Kunde wurde gelöscht.');
            return $this->redirectToRoute('/customers');
        } else {
            self::$status->add('errors', 'Es ist ein Fehler beim Löschen aufgetreten!');
            return $this->redirectToRoute("customer/{$this->customer->getId()}");
        }
    }

    public function new()
    {
        $this->customer = Customer::new();
        $this->view->assign('customer', $this->customer);
    }
}
