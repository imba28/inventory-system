<?php
namespace App\Models;

use App\File\Image;

class ProductImage extends Model
{
    protected $attributes = ['product', 'title', 'src'];

    public function init()
    {
        $this->on(
            'delete',
            function () {
                Image::delete($this->get('src'));
            }
        );
    }
}
