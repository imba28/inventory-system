<?php
namespace App\Controller;

use App\Models\User;

class SessionController extends ApplicationController
{
    public function loginForm()
    {
        if ($this->isUserSignedIn()) {
            $this->response->redirect('/');
        }
        $this->view->setTemplate('login-form');
    }

    public function login()
    {
        if ($this->isUserSignedIn()) {
            self::$status ->add('errors', 'Du bist bereits eingeloggt!');
            $this->redirectToRoute('/');
        } else {
            $this->loginForm();

            try {
                $user = User::find($this->request->get('username'), 'username');

                if (password_verify($this->request->get('password'), $user->get('password'))) {
                    $_SESSION['user_id'] = $user->getId();

                    self::$status ->add('success', "Willkommen zurück {$user->get('name')}!");
                    $this->redirectToRoute('/');
                } else {
                    self::$status ->add('errors', 'Benutzer/Passwort ist falsch!');
                }
            } catch (\App\Exceptions\NothingFoundException $e) {
                self::$status ->add('errors', 'Benutzer/Passwort ist falsch!');
            }
        }
    }

    public function logout()
    {
        session_destroy();
        $_SESSION = array();

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            0,
            $params['path'],
            $params['domain'],
            $params['secure'],
            isset($params['httponly'])
        );

        self::$status->add('info', 'Erfolgreich ausgeloggt!');
        $this->redirectToRoute('/');
    }

    public function error($status)
    {
    }
}
