<?php
namespace App\Controllers;

use App\Bootstrap\Bootstrap;
use \App\Helper\Messages\MessageCollection;
use App\Helper\Loggers\Logger;
use App\Views\Factory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BasicController
{
    protected $response;
    protected $view;

    protected $responseType;
    protected $beforeActions;
    
    protected static $status;

    private $formats = array();

    final public function __construct(RequestStack $requestStack, Factory $viewFactory, $responseType = 'html', $layout = 'default')
    {
        $this->responseType = $responseType;
        $this->response = new \App\HttpResponse();
        $this->view = $viewFactory->build($responseType, $layout);

        if (!isset(self::$status)) {
            self::$status = new MessageCollection();
        }

        $this->beforeActions = array();

        $this->ifResponseType(
            'html',
            function () use($requestStack) {
                $this->view->assign('request', $requestStack->getMasterRequest());
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
     * Sets the view's template file.
     *
     * @param mixed $templateName
     * @return void
     */
    public function setViewTemplate($templateName)
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
    public function callFormats()
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
    public function callBeforeActions($method, $args)
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
     * @todo: while the original method forwarded the request to another controller we are using a hard redirect. this might break stuff?
     * @param mixed $requestMethod
     */
    protected function redirectToRoute($route)
    {
        return new RedirectResponse($route);
    }

    public function getView()
    {
        return $this->view;
    }
}
