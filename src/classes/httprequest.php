<?php
namespace App;

class HttpRequest implements \App\Interfaces\Request {
    private $params;

    public function __construct() {
        $this->params = $_REQUEST;
    }

    public function issetParam($param) {
        return isset($this->params[$param]);
    }

    public function getParam($param) {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }

    public function getParams() {
        return array_keys($this->params);
    }

    public function getHeader($name) {
        $name = 'HTTP_'.strtoupper(str_replace('-', '_', $name));
        return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
    }
}
?>