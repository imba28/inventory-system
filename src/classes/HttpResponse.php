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

    public function redirect($location) {
        $this->setStatus(301);
        $this->addHeader('Location', $location);
        $this->flush();

        exit();
    }

    public function flush() {
        $this->addHeader('Content-Length', strlen($this->body));

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