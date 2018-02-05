<?php
namespace App\Controller;

abstract class ApplicationController extends \App\BasicController {
    private $currentUser;

    public function __construct($responseType = 'html', $layout = 'default') {
        parent::__construct($responseType, $layout);

        $this->format('html', function() {
            $this->view->assign('currentUser', $this->getCurrentUser());
            $this->view->assign('isUserSignedIn', $this->isUserSignedIn());
        });

        if($this->isUserSignedIn()) {
            \App\Menu::getInstance()->set('items', array(
                'Produkte' => 'products',
                'Kunden' => 'customers',
                'Inventur' => 'inventur'
            ));
        }
        else {
            \App\Menu::getInstance()->set('items', array(
                'Produkte' => 'products'
            ));
        }
    }

    protected function getCurrentUser() {
        if(isset($_SESSION['user_id'])) {
            if(is_null($this->currentUser)) {
                try {
                    $this->currentUser = \App\Models\User::grab($_SESSION['user_id']);
                }
                catch(\App\Exceptions\NothingFoundException $e) {
                    return null;
                }
            }
            return $this->currentUser;
        }

        return null;
    }

    protected function isUserSignedIn() {
        return !is_null($this->getCurrentUser());
    }

    protected function redirectToRoute($route) {
        \App\Router::getInstance()->route($route);
    }

    protected function authenticateUser() {
        if(!$this->isUserSignedIn()) {
            $this->response->redirect('/');
        }
    }
}
?>