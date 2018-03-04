<?php
namespace App\Views;

use App\Configuration;

class View
{
    protected $data = array();
    private $template;

    public function assign($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function setTemplate($template)
    {
        $this->template = strtolower($template);
    }

    public function render($layout = 'default')
    {
        $templateFile = "{$this->template}.html.twig";
        $templateHead = "/base/{$layout}-head.html.twig";
        $templateFooter = "/base/{$layout}-footer.html.twig";

        if (file_exists(ABS_PATH."/src/views/" . $templateFile)) {
            $loader = new \Twig_Loader_Filesystem(ABS_PATH."/src/views/");

            $twigOptions = array();

            if (Configuration::get('env') === 'dev') {
                $twigOptions['debug'] = true;
            } else {
                $twigOptions['cache'] = ABS_PATH . '/cache/twig';
            }

            $twig = new \Twig_Environment($loader, $twigOptions);

            $twig->addFilter(
                new \Twig_Filter(
                    'ago',
                    function ($string) {
                        return ago($string);
                    }
                )
            );

            $twig->addExtension(new \Twig_Extension_Debug());

            $html = '';

            $html .= $twig->render($templateHead, $this->data);
            $html .= $twig->render($templateFile, $this->data);
            $html .= $twig->render($templateFooter, $this->data);

            return $html;
        }
        throw new \Exception("Template `{$this->template}` not found!");
    }

    public function getContentType()
    {
        return 'text/html';
    }

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
