<?php
namespace App\Controller;

abstract class ApplicationController extends \App\BasicController
{
    private $currentUser;

    public function init()
    {
        $this->ifResponseType(
            'html',
            function () {
                $this->view->assign('currentUser', $this->getCurrentUser());
                $this->view->assign('isUserSignedIn', $this->isUserSignedIn());
                $this->view->assign('siteName', \App\Configuration::get('site_name'));

                if ($this->isUserSignedIn()) {
                    $this->view->assign(
                        'menuItems',
                        array(
                            'Produkte' => 'products',
                            'Kunden' => 'customers',
                            'Inventur' => 'inventur'
                        )
                    );
                } else {
                    $this->view->assign(
                        'menuItems',
                        array(
                            'Produkte' => 'products'
                        )
                    );
                }
            }
        );
    }

    protected function getCurrentUser()
    {
        if (isset($_SESSION['user_id'])) {
            if (is_null($this->currentUser)) {
                try {
                    $this->currentUser = \App\Models\User::find($_SESSION['user_id']);
                } catch (\App\Exceptions\NothingFoundException $e) {
                    return null;
                }
            }
            return $this->currentUser;
        }

        return null;
    }

    protected function isUserSignedIn()
    {
        return !is_null($this->getCurrentUser());
    }

    protected function authenticateUser()
    {
        if (!$this->isUserSignedIn()) {
            $this->response->redirect('/login');
        }
    }
}
