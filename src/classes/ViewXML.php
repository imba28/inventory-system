<?php
namespace App;

class ViewXML extends View
{
    public function render($layout = null)
    {
        return $this->generateValidXml($this->data);
    }

    public function getContentType()
    {
        return 'application/xml';
    }


    private function generateValidXml($arg)
    {
        $arg = json_decode(json_encode($arg)); // naja :D
        if (is_array($arg)) {
            return self::generateValidXmlFromArray($arg);
        } else {
            return self::generateValidXmlFromObj($arg);
        }
    }
    // functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/
    private function generateValidXmlFromObj(\stdClass $obj, $nodeBlock = 'nodes', $nodeName = 'node')
    {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $nodeBlock, $nodeName);
    }

    private function generateValidXmlFromArray($array, $nodeBlock = 'nodes', $nodeName = 'node')
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $nodeBlock . '>';
        $xml .= self::generateXmlFromArray($array, $nodeName);
        $xml .= '</' . $nodeBlock . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $nodeName)
    {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $nodeName;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $nodeName) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }
}
