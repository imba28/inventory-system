<?php
namespace App\Models;

class User extends \App\Model
{
    protected $attributes = ['name', 'username', 'password'];
    protected $name;
    protected $username;
    protected $password;

    protected function int()
    {
        $this->on(
            'save',
            function ($e) {
                $user = $e->getContext();

                $password = self::getHashedString($user->get('password'));

                $user->set('password', $password);
            }
        );
    }

    public static function getHashedString($string): string
    {
        return password_hash($string, PASSWORD_BCRYPT);
    }

    public function jsonSerialize(): array
    {
        $json = array();

        foreach (array(
            'id' => $this->getId(),
            'name' => $this->get('name'),
            'username' => $this->get('username')
        ) as $key => $value) {
            if ($this->get($key) instanceof \App\Model) {
                $value = $value->jsonSerialize();
            }

            $json[$key] = $value;
        }

        return $json;
    }
}
