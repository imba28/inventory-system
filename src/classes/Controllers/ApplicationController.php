<?php
namespace App\Controllers;

abstract class ApplicationController extends BasicController
{
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

    /**
     * @deprecated - use $this->getUser()
     */
    protected function getCurrentUser()
    {
        return $this->getUser();
    }

    /**
     * @deprecated - use $this->getUser() !== null instead
     */
    protected function isUserSignedIn()
    {
        return !is_null($this->getCurrentUser());
    }

}
