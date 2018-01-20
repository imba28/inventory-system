<?php
namespace App\Traits;

trait Events {
    private $events = array();

    protected final function on($eventName, \Closure $closure) {
        if(!isset($this->events[$eventName])) $this->events[$eventName] = array();
        if(!in_array($closure, $this->events[$eventName])) {
            $this->events[$eventName][] = $closure;
        }
        else throw new InvalidArgumentException('Closure already added!');
    }

    protected final function trigger($eventName, $context = null, $info = array()) {
        if(isset($this->events[$eventName])) {
            $event = $context;
            if(!$event instanceof \App\Event) {
                $event = new \App\Event($eventName, $context, $info);
            }

            foreach($this->events[$eventName] as $eventHandler) {
                $eventHandler($event);
            }
        }
    }
}
?>