<?php
namespace App\Traits;

trait Singleton {
    protected static $instance = null;

    protected final function __construct() {
        $this->init();
    }
    protected final function __clone() { }
    protected function init() { }

    public static function getInstance() {
        if(is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }
}
?>