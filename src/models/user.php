<?php
namespace App\Models;

class User extends \App\Model {
    protected $name;
    protected $username;
    protected $password;

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->on('save', function($e){
            $user = $e->getContext();

            $password = $user->get('password');
            $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
            $salt = sprintf("$2a$%02d$", 10) . $salt;
            $password = crypt($password, $salt);

            $user->set('password', $password);
        });
    }
}
?>