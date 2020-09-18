<?php
namespace App\Models;

use Symfony\Component\Security\Core\User\UserInterface;

class User extends Model implements UserInterface
{
    protected $attributes = ['name', 'username', 'password', 'email'];
    protected $name;
    protected $username;
    protected $email;
    protected $password;

    public function jsonSerialize(): array
    {
        $json = array();

        foreach (array(
            'id' => $this->getId(),
            'name' => $this->get('name'),
            'username' => $this->get('username')
        ) as $key => $value) {
            if ($this->get($key) instanceof \App\Models\Model) {
                $value = $value->jsonSerialize();
            }

            $json[$key] = $value;
        }

        return $json;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return ['ROLE_USER', 'ROLE_ADMIN'];
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->get('password');
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // noop
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->get('username');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // noop
    }
}
