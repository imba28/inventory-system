<?php
namespace App\Controllers;

use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SessionController extends ApplicationController
{
    public function loginForm()
    {
        if ($this->isUserSignedIn()) {
            return $this->response->redirect('/');
        }
        $this->view->setTemplate('login-form');

        return new Response($this->view->render());
    }

    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        vd($error);
        vd($lastUsername);
        die();

        self::$status ->add('errors', 'Benutzer/Passwort ist falsch!');

        /*
        if ($this->isUserSignedIn()) {
            self::$status ->add('errors', 'Du bist bereits eingeloggt!');
            return $this->redirectToRoute('/');
        } else {
            $this->loginForm();

            try {
                $user = User::find($request->get('username'), 'username');

                if (password_verify($request->get('password'), $user->get('password'))) {
                    $_SESSION['user_id'] = $user->getId();

                    self::$status ->add('success', "Willkommen zurück {$user->get('name')}!");
                    return $this->redirectToRoute('/');
                } else {
                    self::$status ->add('errors', 'Benutzer/Passwort ist falsch!');
                }
            } catch (\App\Exceptions\NothingFoundException $e) {
                self::$status ->add('errors', 'Benutzer/Passwort ist falsch!');
            }
        }*/
    }

    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');

        /*session_destroy();
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
        return $this->redirectToRoute('/');*/
    }

    public function error($status)
    {
    }
}
