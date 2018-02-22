<?php
namespace App\Controller;

use App\Models\Customer;
use App\Models\Action;
use App\System;

class CustomerController extends ApplicationController
{
    private $customer;

    public function init()
    {
        parent::init();
        
        $this->authenticateUser();

        $this->beforeAction(array('show', 'update', 'delete', 'edit'), function ($params) {
            try {
                $this->customer = Customer::find($params['id']);
                $this->view->assign('customer', $this->customer);
            } catch (\App\Exceptions\NothingFoundException $e) {
                $this->error(404);
            }
        });
    }

    public function index($params = array())
    {
        $this->respondTo(function ($wants) use ($params) {
            $wants->html(function () use ($params) {
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
                    System::error('Keine Ergebnisse gefunden!');
                }

                $this->view->setTemplate('customers');
            });

            $wants->json(function () {
                $this->view->assign('customers', Customer::all());
            });

            $wants->xml(function () {
                $this->view->assign('customers', Customer::all());
            });
        });
    }

    public function show(array $params)
    {
        $this->respondTo(function ($wants) {
            $wants->html(function () {
                try {
                    $rentalHistory = Action::findByFilter(array(
                        array('customer', '=', $this->customer)
                    ), 10, array('returnDate' => 'ASC'));
                } catch (\App\Exceptions\NothingFoundException $e) {
                    $rentalHistory = array();
                }

                $this->view->assign('rentHistory', $rentalHistory);
                $this->view->setTemplate('customer');
            });
        });
    }

    public function update()
    {
        $this->customer->setAll($this->request->getParams());
 
        try {
            $this->customer->save();
            System::success(
                "<a href='/customer/{$this->customer->getId()}'>
                    {$this->customer->get('name')}
                </a>
                wurde gespeichert!"
            );
        } catch (\App\QueryBuilder\QueryBuilderException $e) {
            list($error_code, $error_message, $error_value, $error_column) = $e->getData();

            System::error($error_message);
        } catch (\App\QueryBuilder\NothingChangedException $e) {
            //System::info('Es wurde nichts geändert.');
        } catch (\InvalidOperationException $e) {
            System::error('Fehler beim Speichern! ' . $e->getMessage());
        } catch (\App\Exceptions\InvalidModelDataException $e) {
            System::error(join(', ', $this->customer->getErrors()));
        } catch (\Exception $e) {
            System::error('Fehler beim Speichern!');
        }

        $this->edit(); // show edit form
    }

    public function edit()
    {
        $this->view->setTemplate('customer-update');
    }

    public function create()
    {
        $this->new();
        $this->update();
        $this->view->setTemplate('customer-add');
    }

    public function delete()
    {
        if ($this->customer->remove()) {
            System::success('Kunde wurde gelöscht.');
        } else {
            System::error('Es ist ein Fehler beim Löschen aufgetreten!');
        }

        $this->index();
    }

    public function new()
    {
        $this->customer = Customer::new();
        $this->customer->set('user', $this->getCurrentUser());
        $this->view->assign('customer', $this->customer);

        $this->view->setTemplate('customer-add');
    }

    public function error($status, $message = "Dieser Kunde konnte leider nicht gefunden werden!")
    {
        $this->response->setStatus($status);
        $this->view->setTemplate('error');
        $this->view->assign('errorCode', $status);
        $this->view->assign('errorMessage', $message);

        $this->renderContent();
        exit();
    }
}
