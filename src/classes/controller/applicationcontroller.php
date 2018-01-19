<?php
namespace App\Controller;

abstract class ApplicationController extends \App\BasicController {
    private $currentUser;

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
}
?>