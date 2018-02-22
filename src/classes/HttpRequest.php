<?php
namespace App;

class HttpRequest implements \App\Interfaces\Request
{
    private $params;
    private $files;

    public function __construct()
    {
        $this->params = $_REQUEST;
        $this->files = $_FILES;
    }

    public function issetParam($param)
    {
        return isset($this->params[$param]);
    }

    public function get($param)
    {
        // Alias für Views
        return $this->getParam($param);
    }

    public function getParam($param)
    {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getHeader($name)
    {
        $name = strtoupper($name);
        return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
    }

    public function issetFile($name = null)
    {
        return is_null($name) ? !empty($_FILES) : isset($_FILES[$name]);
    }

    public function getFile($name)
    {
        return $this->issetFile($name) ? $_FILES[$name] : null;
    }

    public function getFiles($name)
    {
        if (isset($_FILES[$name])) {
            $files = array();

            for ($i = 0; $i < count($_FILES[$name]["name"]); $i++) {
                $f = array();
                foreach (array_keys($_FILES[$name]) as $key) {
                    $f[$key] = $_FILES[$name][$key][$i];
                }
                $files[] = $f;
            }

            return $files;
        }
        return null;
    }
}
