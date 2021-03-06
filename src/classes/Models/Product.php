<?php
namespace App\Models;

use App\Collection;

class Product extends Model
{
    protected $attributes = ['name', 'invNr', 'type', 'description', 'condition', 'note'];
    protected $validators = ['name' => 'required', 'invNr' => 'required'];

    public function images(): Collection
    {
        return $this->hasMany('ProductImage');
    }

    public function getFrontImage(): Model
    {
        foreach ($this->images() as $image) {
            if ($image->get('title') == 'frontimage') {
                return $image;
            }
        }

        if (!$this->images()->isEmpty()) {
            return $this->images()->first();
        }

        return new ProductImage(
            array(
            'src' => 'http://via.placeholder.com/200x200',
            'title' => 'frontimage'
            )
        );
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
            $action = \App\Models\Action::findByFilter(
                array(
                array('product', '=', $this),
                array('returnDate', 'IS', 'NULL')
                )
            );
            return count($action) == 0;
        } catch (\App\Exceptions\NothingFoundException $e) {
            return true;
        }
    }

    public function getRentalAction(): \App\Models\Action
    {
        return \App\Models\Action::findByFilter(
            array(
            array('product', '=', $this),
            array('returnDate', 'IS', 'NULL')
            ),
            1
        );
    }

    public function rent(Customer $customer, string $returnDate, User $user)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $returnDate = empty($returnDate) ? null : $returnDate;

        $action = Action::new();
        $action->set('product', $this);
        $action->set('customer', $customer);
        $action->set('rentDate', 'now');
        $action->set('expectedReturnDate', $returnDate);
        $action->set('user', $user);

        return $action->save();
    }

    public function jsonSerialize(): array
    {
        $json = $this->get();
        $json['images'] = array();

        foreach ($this->images() as $image) {
            $object = new \stdClass();
            foreach ($image->get() as $key => $value) {
                if ($value instanceof \App\Models\Model) {
                    continue;
                }
                $object->$key = $value;
            }

            $json['images'][] = $object;
        }

        return $json;
    }
}
