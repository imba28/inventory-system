<?php
namespace App\Views;

use App\Configuration;
use Twig\Environment;

abstract class View
{
    protected $data = array();

    public function assign($key, $value)
    {
        $this->data[$key] = $value;
    }

    abstract function render($layout = null);
    abstract function getContentType();

    /*
    private function bufferContent($path)
    {
        extract($this->data);

        ob_start();
        include $path;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }*/
}
