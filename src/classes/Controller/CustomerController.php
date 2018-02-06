<?php
namespace App\Controller;

class CustomerController extends ApplicationController
{
    private $customer;

    public function __construct($responseType = 'html', $layout = 'default')
    {
        parent::__construct($responseType, $layout);

        $this->authenticateUser();

        $this->beforeAction(array('show', 'update', 'delete', 'edit'), function ($params) {
            try {
                $this->customer = \App\Models\Customer::find($params['id']);
                $this->view->assign('customer', $this->customer);
            } catch (\App\Exceptions\NothingFoundException $e) {
                $this->error(404);
            }
        });
    }

    public function index($params = array())
    {
        $customers = \App\Models\Customer::all();
        $this->view->assign('customers', $customers);
        $this->view->setTemplate('customers');
    }

    public function show(array $params)
    {
        $this->respondTo(function ($wants) {
            $wants->html(function () {
                try {
                    $rentalHistory = \App\Models\Action::findByFilter(array(
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
        $params = $this->request->getParams();
        foreach ($params as $key) {
            $this->customer->set($key, $this->request->getParam($key));
        }

        if (empty($this->customer->get('name')) || empty($this->customer->get('internal_id'))) {
            \App\System::getInstance()->addMessage('error', 'Name/FHS Nummer muss angegeben werden!');
        } else {
            try {
                $this->customer->save();
                \App\System::getInstance()->addMessage('success', "<a href='/customer/{$this->customer->getId()}'>{$this->customer->get('name')}</a> wurde gespeichert!");
            } catch (\App\QueryBuilder\QueryBuilderException $e) {
                list($error_code, $error_message, $error_value, $error_column) = $e->getData();

                \App\System::getInstance()->addMessage('error', $error_message);
            } catch (\App\QueryBuilder\NothingChangedException $e) {
                //\App\System::getInstance()->addMessage('info', 'Es wurde nichts geändert.');
            } catch (\InvalidOperationException $e) {
                \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern! ' . $e->getMessage());
            } catch (\Exception $e) {
                vd($e);
                \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern!');
            }
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
            \App\System::getInstance()->addMessage('success', 'Kunde wurde gelöscht.');
        } else {
            \App\System::getInstance()->addMessage('error', 'Es ist ein Fehler beim Löschen aufgetreten!');
        }

        $this->index();
    }

    public function new()
    {
        $this->customer = \App\Models\Customer::new();
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
