<?php
namespace App\Models;

use App\Collection;
use App\Model;

class Product extends \App\Model
{
    protected $attributes = ['name', 'invNr', 'type', 'description', 'condition', 'note'];

    public function images(): Collection
    {
        return $this->hasMany('ProductImage');
    }

    public function getFrontImage(): Model
    {
        foreach ($this->images() as $key => $image) {
            if ($image->get('title') == 'frontimage') {
                return $image;
            }
        }

        if (!$this->images()->isEmpty()) {
            return $this->images()->first();
        }

        return new ProductImage(array(
            'src' => 'http://via.placeholder.com/200x200',
            'title' => 'frontimage'
        ));
    }

    public function addImage(ProductImage $image)
    {
        $this->images()->append($image);
    }

    public function getImages()
    {
        return $this->images();
    }

    public function isAvailable()
    {
        try {
            $action = \App\Models\Action::findByFilter(array(
                array('product_id', '=', $this->getId()),
                array('returnDate', 'IS', 'NULL')
            ));
            return count($action) == 0;
        } catch (\App\Exceptions\NothingFoundException $e) {
            return true;
        }
    }

    public function getRentalAction(): \App\Models\Action
    {
        return \App\Models\Action::findByFilter(array(
            array('product', '=', $this),
            array('returnDate', 'IS', 'NULL')
        ), 1);
    }

    public function jsonSerialize(): array
    {
        $json = $this->data;
        $json['images'] = array();

        foreach ($this->images as $image) {
            $object = new \stdClass();
            foreach ($image->data as $key => $value) {
                if ($value instanceof \App\Model) {
                    continue;
                }
                $object->$key = $value;
            }

            $json['images'][] = $object;
        }

        return $json;
    }
}
