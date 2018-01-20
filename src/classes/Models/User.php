<?php
namespace App\Models;

class User extends \App\Model {
    protected $name;
    protected $username;
    protected $password;

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->on('save', function($e) {
            $user = $e->getContext();

            $password = self::getHashedString($user->get('password'));

            $user->set('password', $password);
        });
    }

    public static function getHashedString($string): string {
        return password_hash($string, PASSWORD_BCRYPT);
    }
}
?>