<?php
namespace App\Controller;

class PageController implements \App\Interfaces\Controller {
    protected $response;
    protected $view;

    public function __construct() {
        $this->response = new \App\HttpResponse();
        $this->view = new \App\View();
    }

    public function home() {
        $this->view->assign('customer', \App\Models\Customer::Grab(1));
        $this->view->setTemplate('home');

        $this->response->append($this->view->render());
        $this->response->flush();
    }

    public function error($status) {
        $this->response->setSatus($status);
        $this->view->setTemplate('error');
        $this->response->append($this->view->render());

        $this->response->flush();
    }
}
?>