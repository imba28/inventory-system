<?php
namespace App\Helper\Messages;

interface MessageInterface
{
    public function messages(): MessageCollection;
}
