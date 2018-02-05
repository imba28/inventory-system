<?php
namespace App\Controller;

class SessionController extends ApplicationController {
    public function loginForm() {
        if($this->isUserSignedIn()) {
            $this->response->redirect('/');
        }
        $this->view->setTemplate('login-form');
    }

    public function login() {
        if($this->isUserSignedIn()) {
            \App\System::getInstance()->addMessage('error', 'Du bist bereits eingeloggt!');
            $this->redirectToRoute('/');
        }

        $this->loginForm();

        try {
            $user = \App\Models\User::find($this->request->get('username'), 'username');

            if(password_verify($this->request->get('password'), $user->get('password'))) {
                $_SESSION['user_id'] = $user->getId();

                \App\System::getInstance()->addMessage('success', "Willkommen zurück {$user->get('name')}!");
                $this->redirectToRoute('/');
            }
            else {
                \App\System::getInstance()->addMessage('error', 'Passwort ist falsch!');
            }
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            \App\System::getInstance()->addMessage('error', 'Benutzer wurde nicht gefunden!');
        }
    }

    public function logout() {
        session_destroy();
        $_SESSION = array();

        $params = session_get_cookie_params();
        setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));

        \App\System::getInstance()->addMessage('info', 'Erfolgreich ausgeloggt!');
        $this->redirectToRoute('/');
    }

    public function error($status) {

    }
}
?>