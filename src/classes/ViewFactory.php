<?php
namespace App;

class ViewFactory
{
    public static function build($responseType)
    {
        switch ($responseType) {
            case 'html':
            default:
                return new View();
            case 'json':
                return new ViewJSON();
            case 'xml':
                return new ViewXML();
        }
    }
}
