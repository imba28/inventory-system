<?php
namespace App\Controllers;

abstract class ApplicationController extends BasicController
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
        return $this->getUser();
    }

    protected function isUserSignedIn()
    {
        return !is_null($this->getCurrentUser());
    }

    protected function authenticateUser()
    {
        if (!$this->isUserSignedIn()) {
            return $this->redirectTo('/login');
        }
    }
}
