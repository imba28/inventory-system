<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SessionController extends ApplicationController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $this->view->setTemplate('login-form');
        $this->view->assign('error', $error);
        $this->view->assign('lastUsername', $lastUsername);

        return new Response($this->view->render());
    }


    /*
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
        }
    }
    */

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function error($status)
    {

    }
}
