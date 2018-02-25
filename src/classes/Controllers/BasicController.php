<?php
namespace App\Controllers;

use \App\Helper\Messages\MessageCollection;
use App\Helper\Loggers\Logger;

abstract class BasicController
{
    protected $layout;
    protected $response;
    protected $view;

    protected $responseType;
    protected $beforeActions;
    
    protected static $status;

    private $formats = array();

    final public function __construct($responseType = 'html', $layout = 'default')
    {
        $this->layout = $layout;
        $this->responseType = $responseType;
        $this->response = new \App\HttpResponse();
        $this->request = new \App\HttpRequest();
        $this->view = \App\Views\Factory::build($responseType, $layout);

        if (!isset(self::$status)) {
            self::$status = new MessageCollection();
        }

        $this->beforeActions = array();

        $this->ifResponseType(
            'html',
            function () {
                $this->view->assign('request', $this->request);
                $this->view->assign('status', self::$status);
            }
        );
    }

    /**
     * Helper method that is called inside constructor. May be overridden/extended from sub classes.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Renders view, adds it to the response object and flushes it afterwards.
     *
     * @return void
     */
    protected function renderContent()
    {
        $this->response->append($this->view->render());
        $this->response->addHeader('Content-Type', $this->view->getContentType());
        $this->response->flush();
    }

    /**
     * specifies a function, that should be called before a specific controller method is executed.
     *
     * @param mixed $method
     * @param mixed $methodToCall
     * @return void
     */
    protected function beforeAction($method, $methodToCall)
    {
        if (is_array($method)) {
            foreach ($method as $m) {
                $this->beforeAction($m, $methodToCall);
            }
            return;
        }

        if (!isset($this->beforeActions[$method])) {
            $this->beforeActions[$method] = array();
        }
        $this->beforeActions[$method][] = $methodToCall;
    }

    /**
     * Executes a controller method, that was passed to the method.
     *
     * Tries to find the correct template based on controller and method name.
     *
     * @param mixed $method
     * @param mixed $args
     * @return void
     */
    public function handle($method, $args)
    {
        $this->setViewTemplate($method);
        
        $this->callFormats();
        $this->callBeforeActions($method, $args);
        $this->$method($args);

        $this->renderContent();
    }

    /**
     * Sets the view's template file.
     *
     * @param mixed $templateName
     * @return void
     */
    private function setViewTemplate($templateName)
    {
        $selfClass = get_called_class();

        if (preg_match('/([^\\\]+)Controller$/', $selfClass, $m)) {
            $dir = strtolower($m[1]);
            if (file_exists(ABS_PATH . "/src/views/{$dir}/{$templateName}.html.twig")) {
                $this->view->setTemplate("{$dir}/{$templateName}");
            }
        }
    }

    /**
     * Executes all closures whose response type matches $responseType.
     *
     * @see $responseType
     * @see ifResponseType()    For setting closures
     *
     * @return void
     */
    private function callFormats()
    {
        if (isset($this->formats[$this->responseType])) {
            foreach ($this->formats[$this->responseType] as $f) {
                $f();
            }
        }
    }

    /**
     * Sets a closure that should only be executed in case of a specific response type.
     *
     * @param string $responseType
     * @param \Closure $f
     * @return void
     */
    protected function ifResponseType(string $responseType, \Closure $f)
    {
        $this->formats[$responseType][] = $f;
    }

    /**
     * Executes all closures that were specified by beforeAction().
     *
     * @param mixed $method
     * @param mixed $args
     * @return void
     */
    private function callBeforeActions($method, $args)
    {
        if (isset($this->beforeActions[$method])) {
            foreach ($this->beforeActions[$method] as $function) {
                if ($function instanceof \Closure) {
                    call_user_func_array($function, array($args));
                } elseif (is_callable(array($this, $function), false)) {
                    $this->$function($args);
                } elseif (function_exists($function)) {
                    $function($args);
                } else {
                    Logger::warn("Cannot find method to call `{$function}`!");
                }
            }
        }
    }

    /**
     * Executes code blocks depending on on what response type the client expects.
     *
     * @see \App\Format For all possible response types.
     *
     * @param \Closure $formats
     * @return void
     */
    protected function respondTo(\Closure $formats)
    {
        /*
        kapselt verschiedene Closures, die je nach response Type Werte in der View setzen.
        example usage:

        $this->respondTo(function($wants) {
            $wants->html(function() {
                $this->view->assign('rentHistory', $rentalHistory);
                $this->view->setTemplate('customer');
            });

            $wants->json(function() {
                $this->view->assign('url', $url);
            });
        });
        */
        $responseFormats = new \App\Format();
   
        $formats($responseFormats);
        $responseFormats->execute($this->responseType);
    }

    /**
     * Calls the router with a passed route and request method.
     *
     * @param mixed $route
     * @param mixed $requestMethod
     * @return void
     */
    protected function redirectToRoute($route, $requestMethod = 'GET')
    {
        \App\Routing\Router::getInstance()->route($route, $requestMethod);
    }

    abstract public function error($status);
}
