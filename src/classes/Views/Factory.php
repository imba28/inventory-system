<?php
namespace App\Views;

class Factory
{
    private ViewHTML $htmlView;

    private ViewJSON $jsonView;

    private ViewXML $xmlView;

    public function __construct(ViewHTML $view, ViewJSON $jsonView, ViewXML $xmlView)
    {
        $this->htmlView = $view;
        $this->jsonView = $jsonView;
        $this->xmlView = $xmlView;
    }

    public function build($responseType)
    {
        switch ($responseType) {
            case 'html':
            default:
                return $this->htmlView;
            case 'json':
                return $this->jsonView;
            case 'xml':
                return $this->xmlView;
        }
    }
}
