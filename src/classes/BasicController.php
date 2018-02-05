<?php
namespace App;

abstract class BasicController {
    protected $layout;
    protected $response;
    protected $view;

    protected $responseType;
    protected $beforeActions;

    public function __construct($responseType = 'html', $layout = 'default') {
        $this->layout = $layout;
        $this->responseType = $responseType;
        $this->response = new \App\HttpResponse();
        $this->request = new \App\HttpRequest();
        $this->view = \App\ViewFactory::build($responseType, $layout);

        $this->beforeActions = array();

        $this->view->assign('request', $this->request);
    }

    protected function renderContent() {
        $this->response->append($this->view->render());
        $this->response->addHeader('Content-Type', $this->view->getContentType());
        $this->response->flush();
    }

    protected function beforeAction($method, $methodToCall) {
        if(is_array($method)) {
            foreach($method as $m) {
                $this->beforeAction($m, $methodToCall);
            }
            return;
        }

        if(!isset($this->beforeActions[$method])) $this->beforeActions[$method] = array();
        $this->beforeActions[$method][] = $methodToCall;
    }

    public function handle($method, $args) {
        $this->callBeforeActions($method, $args);
        $this->$method($args);

        $this->renderContent();
        exit();
    }

    private function callBeforeActions($method, $args) {
        if(isset($this->beforeActions[$method])) {
            foreach($this->beforeActions[$method] as $function)  {
                if($function instanceof \Closure) {
                    call_user_func_array($function, array($args));
                }
                elseif(is_callable(array($this, $function), false, $callableName)) {
                    $this->$function($args);
                }
                elseif(function_exists($function)) {
                    $function($args);
                }
                else \App\Debugger::log('warning', "Cannot find method to call `{$function}`!");
            }
        }
    }

    abstract public function error($status);
}

?>