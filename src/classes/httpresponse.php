<?php
namespace App;

class HttpResponse implements \App\Interfaces\Response {
    private $status = '200 OK';
    private $headers = array();
    private $body = null;

    public function setStatus($status) {
        $this->status = $status;
    }
    public function addHeader($key, $value) {
        $this->headers[$key] = $value;
    }
    public function append($data) {
        $this->body .= $data;
    }

    public function flush() {
        if(!headers_sent()) {
            header("HTTP/1.1 {$this->status}");
            foreach($this->headers as $key => $value) {
                header("{$key}: {$value}");
            }
        }

        echo $this->body;

        $this->headers = array();
        $this->body = null;
    }
}
?>