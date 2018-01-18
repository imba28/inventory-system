<?php
namespace App\Models;

class Product extends \App\Model {
    protected $name;
    protected $invNr;
    protected $type;
    protected $description = null;
    protected $condition = null;
    protected $note = null;

    public function __construct($options = array()) {
        parent::__construct($options);

        if($this->isCreated()) {
            try {
                $this->images = ProductImage::grabByFilter(array(
                    array('product', '=', $this)
                ), false, array('id' => 'ASC'));
            }
            catch( \App\Exceptions\NothingFoundException $e) {
                $this->images = array();
            }
        }
    }

    public function getFrontImage() {
        foreach($this->images as $image) {
            if($image->get('title') == 'frontimage') return $image;
        }

        if(!empty($this->images)) return $this->images[0];

        return new ProductImage(array(
            'src' => 'http://via.placeholder.com/200x200',
            'title' => 'frontimage'
        ));
    }
    public function addImage(ProductImage $image) {
        $this->images[] = $image;
    }
    public function getImages() {
        return $this->images;
    }

    public function save($head_column = null, $head_id = null, $exception = false) {
        if(!empty($this->images)) {
            $images = $this->images;
            unset($this->images);

            parent::save($head_column, $head_id);

            foreach($images as $image) {
                $image->save('product_id', $this->getId());
            }

            $this->images = $images;
        }
        else {
            parent::save($head_column, $head_id, $exception);
        }
    }

    public function isAvailable() {
        try {
            $action = \App\Models\Action::grabByFilter(array(
                array('product_id', '=', $this->id),
                array('returnDate', 'IS', 'NULL')
            ));
            return count($action) == 0;
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            return true;
        }
    }
}
?>