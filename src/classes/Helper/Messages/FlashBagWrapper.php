<?php


namespace App\Helper\Messages;


use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FlashBagWrapper extends MessageCollection
{
    private FlashBagInterface $flashBag;

    public function __construct(SessionInterface $session)
    {
        $this->flashBag = $session->getFlashBag();
    }

    public function add($key, $message)
    {
        $this->flashBag->add($key, $message);
    }

    public function get($key): array
    {
        return $this->flashBag->get($key);
    }

    public function any(): bool
    {
        return true;
    }
}
