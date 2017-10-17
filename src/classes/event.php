<?php
namespace App;

class Event {
    protected $eventName;
    protected $eventContext;
    protected $eventInfo;
    protected $canceled = false;

    public function __construct($eventName, $eventContext = null, $eventInfo = null) {
        $this->eventName = $eventName;
        $this->eventContext = $eventContext;
        $this->eventInfo = $eventInfo;
    }

    public function getContext() {
        return $this->eventContext;
    }
    public function getInfo() {
        return $this->eventContext;
    }
    public function setInfo(array $a) {
        $this->eventInfo = $a;
    }

    public function isCanceled() {
        return $this->canceled;
    }
    public function cancel() {
        $this->canceled = true;
    }
}
?>