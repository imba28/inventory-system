<?php
namespace App;

abstract class BasicController {
    protected $layout;
    protected $response;
    protected $view;

    public function __construct($layout = 'default') {
        $this->layout = $layout;
        $this->response = new \App\HttpResponse();
        $this->request = new \App\HttpRequest();
        $this->view = new \App\View();

        $this->view->assign('request', $this->request);
    }

    protected function renderContent() {
        $this->response->append($this->getLayoutComponent('head'));
        $this->response->append($this->view->render());
        $this->response->append($this->getLayoutComponent('footer'));
        $this->response->flush();
    }

    protected function bufferContent($path) {
        ob_start();
        include($path);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    protected function getLayoutComponent($type = 'head') {
        if(file_exists(ABS_PATH."/src/layouts/{$this->layout}-{$type}.php")) {
            return $this->bufferContent(ABS_PATH."/src/layouts/{$this->layout}-{$type}.php");
        }
        throw new \InvalidArgumentException("Layout {$this->layout}-{$type}` does not exists!`");
    }

    public function handle($method, $args) {
        $this->$method($args);

        $this->renderContent();
        exit();
    }

    protected function renderJson($obj) {
        $this->response->append(json_encode($obj));
        $this->response->addHeader('Content-Type', 'application/json');
        $this->response->flush();
        exit();
    }

    abstract public function error($status);
}

?>