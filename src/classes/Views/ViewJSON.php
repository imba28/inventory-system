<?php
namespace App\Views;


class ViewJSON extends View
{
    public function render($layout = null)
    {
        if (count($this->data) == 1) {
            return json_encode(current($this->data));
        }
        
        return json_encode($this->data);
    }

    public function getContentType()
    {
        return 'application/json';
    }
}
