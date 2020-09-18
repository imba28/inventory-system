<?php


namespace App\Views;


use Twig\Environment;

class ViewHTML extends View
{
    private $template;

    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function setTemplate($template)
    {
        $this->template = strtolower($template);
    }

    public function render($layout = 'default')
    {
        $templateFile = "{$this->template}.html.twig";

        if (!file_exists(ABS_PATH."/src/views/" . $templateFile)) {
            throw new \Exception("Template `{$this->template}` not found!");
        }

        $this->twig->addFilter(
            new \Twig_Filter(
                'ago',
                function ($string) {
                    return ago($string);
                }
            )
        );

        return $this->twig->render($templateFile, $this->data);
    }

    public function getContentType()
    {
        return 'text/html';
    }
}
