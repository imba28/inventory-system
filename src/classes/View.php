<?php
namespace App;

class View {
    protected $data = array();
    private $template;

    public function assign($key, $value) {
        $this->data[$key] = $value;
    }

    public function setTemplate($template) {
        $this->template = strtolower($template);
    }

    public function render($layout = 'default') {
        $html = $this->getLayoutComponent($layout, 'head');

        $template_file = "{$this->template}.html.twig";
        if(file_exists(ABS_PATH."/src/views/" . $template_file)) {
            $loader = new \Twig_Loader_Filesystem(ABS_PATH."/src/views/");

            $twigOptions = array();

            if(Configuration::get('env') === 'dev') {
                $twigOptions['debug'] = true;
            }
            else {
                $twigOptions['cache'] = ABS_PATH . '/cache/twig';
            }

            $twig = new \Twig_Environment($loader, $twigOptions);

            $twig->addFilter(new \Twig_Filter('ago', function ($string) {
                return ago(strtotime($string));
            }));

            $twig->addExtension(new \Twig_Extension_Debug());


            $html .= $twig->render($template_file, $this->data);
            $html .= $this->getLayoutComponent($layout, 'footer');

            return $html;
            /*extract($this->data);

            ob_start();
            include($template_path);
            $content = ob_get_contents();
            ob_end_clean();

            return $content;*/
        }
        throw new \Exception("Template `{$this->template}` not found!");
    }

    public function getContentType() {
        return 'text/html';
    }

    private function getLayoutComponent($layout, $type = 'head') {
        if(file_exists(ABS_PATH."/src/layouts/{$layout}-{$type}.php")) {
            return $this->bufferContent(ABS_PATH."/src/layouts/{$layout}-{$type}.php");
        }
        throw new \InvalidArgumentException("Layout {$layout}-{$type}` does not exists!`");
    }

    private function bufferContent($path) {
        extract($this->data);

        ob_start();
        include($path);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
?>