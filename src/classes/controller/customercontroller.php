<?php
namespace App\Controller;

class CustomerController extends ApplicationController {
    public function __construct($layout = 'default') {
        parent::__construct($layout);

        $this->authenticateUser();
    }

    public function customers($params) {
        $customers = \App\Models\Customer::grabAll();
        if(@$params['response_type'] === 'json') {
            $response = array();

            foreach($customers as $customer) {
                $response[] = json_decode($customer->toJson());
            }

            $this->response->append(json_encode($response));
            $this->response->addHeader('Content-Type', 'application/json');
            $this->response->flush();
            exit();
        }
        $this->view->assign('customers', $customers);
        $this->view->setTemplate('customers');
    }

    public function customer(array $params) {
        if(isset($params['id'])) {
            $customer = \App\Models\Customer::grab($params['id']);

            if(@$params['response_type'] === 'json') {
                $this->response->append($customer->toJson());
                $this->response->addHeader('Content-Type', 'application/json');
                $this->response->flush();
                exit();
            }
            
            $rentalHistory = array();

            try {
                $rentalHistory = \App\Models\Action::grabByFilter(array(
                    array('customer', '=', $customer)
                ), 10, array('returnDate' => 'ASC'));
            }
            catch(\App\Exceptions\NothingFoundException $e) { }

            $this->view->assign('customer', $customer);
            $this->view->assign('rentHistory', $rentalHistory);
            $this->view->setTemplate('customer');
        }
        else {
            $this->error(404);
        }
    }

    public function action(array $params) {
        if(isset($params['id'])) {
            $customer = \App\Models\Customer::grab($params['id']);
            $this->view->assign('customer', $customer);

            if(isset($params['action'])) {
                if($params['action'] === 'edit') {
                    $this->edit($customer);
                }
                else {
                    $this->error(404);
                }
            }
        }
        elseif(isset($params['action'])) {
            if($params['action'] === 'add') {
                $this->add();
            }
            else {
                $this->error(404);
            }
        }
    }

    private function edit(\App\Models\Customer $customer) {
        $this->view->setTemplate('customer-update');

        if($this->request->issetParam('submit')) {
            $params = $this->request->getParams();
            foreach($params as $key) {
                $customer->set($key, $this->request->getParam($key));
            }

            if(empty($customer->get('name')) || empty($customer->get('internal_id'))) {
                \App\System::getInstance()->addMessage('error', 'Name/FHS Nummer muss angegeben werden!');
                return;
            }

            try {
                $customer->save();
                \App\System::getInstance()->addMessage('success', "<a href='/customer/{$customer->getId()}'>{$customer->get('name')}</a> wurde gespeichert!");
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
                vd($e);
                \App\System::getInstance()->addMessage('error', 'Fehler beim Speichern!');
            }
        }
    }

    public function delete($params) {
        if(isset($params['id'])) {
            if(\App\Models\Customer::delete(intval($params['id']))) {
                \App\System::getInstance()->addMessage('success', 'Kunde wurde gelöscht.');
            }
            else {
                \App\System::getInstance()->addMessage('error', 'Es ist ein Fehler beim Löschen aufgetreten!');
            }

            $this->view->assign('customers', \App\Models\Customer::grabAll());
            $this->view->setTemplate('customers');
        }
    }

    private function add() {
        $this->edit(\App\Models\Customer::new());

        $this->view->setTemplate('customer-add');
    }

    public function error($status) {
        $this->response->setStatus($status);
        $this->view->setTemplate('error');
    }
}
?>