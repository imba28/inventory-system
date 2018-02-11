<?php
namespace App\Models;

class Product extends \App\Model
{
    protected $attributes = ['name', 'invNr', 'type', 'description', 'condition', 'note'];
    {
        parent::__construct($options);
        self::hasMany('ProductImage', 'images');
    }

    public function getFrontImage()
    {
        foreach ($this->images as $key => $image) {
            if ($image->get('title') == 'frontimage') {
                return $image;
            }
        }

        if (!$this->images->isEmpty()) {
            return $this->images->first();
        }

        return new ProductImage(array(
            'src' => 'http://via.placeholder.com/200x200',
            'title' => 'frontimage'
        ));
    }

    public function addImage(ProductImage $image)
    {
        $this->images->append($image);
    }
    public function getImages()
    {
        return $this->images;
    }

    public function save($head_column = null, $head_id = null, $exception = false)
    {
        if (!empty($this->images)) {
            $images = $this->images;
            unset($this->images);

            parent::save($head_column, $head_id);

            foreach ($images as $image) {
                $image->save('product_id', $this->getId());
            }

            $this->images = $images;
        } else {
            parent::save($head_column, $head_id, $exception);
        }
    }

    public function isAvailable()
    {
        try {
            $action = \App\Models\Action::findByFilter(array(
                array('product_id', '=', $this->id),
                array('returnDate', 'IS', 'NULL')
            ));
            return count($action) == 0;
        } catch (\App\Exceptions\NothingFoundException $e) {
            return true;
        }
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
