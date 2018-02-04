<?php
namespace App;

class ViewJSON extends View {
    public function render($layout = null) {
        return json_encode($this->data);
    }

    public function getContentType() {
        return 'application/json';
    }
}
?>