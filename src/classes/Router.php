<?php
namespace App;

class Router
{
    use \App\Traits\Singleton;

    private static $routeCount = 0;

    protected $routes = array(
        'ALL' => array(),
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array()
    );

    public function addRoute($requestMethod, $path, $handler)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->addRoute($requestMethod, $p, $handler);
            }
            return;
        }

        $requestMethod = strtoupper($requestMethod);

        if (in_array($requestMethod, array_keys($this->routes))) {
            $routeOptions = array(
                'regex' => array(),
                'params' => array(),
                'handler' => $handler
            );

            $parts = explode('/', $path);
            foreach ($parts as $part) {
                if (isset($part[0]) && $part[0] === ':') { // dynamic part
                    if ($part[1] === '_') { // _ ist zeichen für Zahl
                        $routeOptions['regex'][] = '([0-9]+)';
                        $routeOptions['params'][] = substr($part, 2);
                    } else {
                        // matches any unicode character, whitespace, minus and comma.
                        $routeOptions['regex'][] = '([\p{N}\p{L}\s\-,]+)';
                        $routeOptions['params'][] = substr($part, 1);
                    }
                } else { // static part
                    if ($part === '*') {
                        $routeOptions['params'][] = $part;
                        $part = '(.+)';
                    }
                    $routeOptions['regex'][] = $part;
                }
            }

            $routeOptions['regex'] = '/^' . implode('\/', $routeOptions['regex']) . '$/ui';

            $this->routes[$requestMethod][self::$routeCount++] = $routeOptions;
        } else {
            throw new \InvalidArgumentException("{$requestMethod} is not a valid http request type!");
        }
    }

    public function handle($handle, $params = array(), $responseType = 'html')
    {
        if ($handle instanceof \Closure) {
            return $handle($params);
        } elseif (is_string($handle)) {
            $split = explode('#', $handle);

            $controller_class = "\App\Controller\\{$split[0]}";
            $controller_action = $split[1];

            $controller = new $controller_class($responseType);
            $controller->init();

            if (is_callable(array($controller, $controller_action), true)) {
                $controller->handle($controller_action, $params);
            } else {
                $controller->error(501, "{$controller_class} does not provide {$controller_action}()!");
            }
        } else {
            throw new \InvalidArgumentException('invalid handle!');
        }
    }

    public function route($requestURI = null, $requestMethod = null)
    {
        if (is_null($requestURI)) {
            $requestURI = urldecode($_SERVER['REQUEST_URI']);
        }
        if (is_null($requestMethod)) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        $uri = ltrim(parse_url($requestURI, PHP_URL_PATH), '/');
        //$params = parse_url($requestURI, PHP_URL_QUERY);

        $routes = $this->routes[$requestMethod] + $this->routes['ALL'];
        ksort($routes);

        if ($this->findHandler($routes, $requestURI) === false) {
            throw new \ErrorException("Route `{$uri}`not defined!");
        }
    }

    private function findHandler(array $routes, $requestURI)
    {
        $params = array();
        $responseType = 'html';

        if (preg_match('/.(json|html|xml)$/', $requestURI, $matches)) {
            $responseType = $matches[1];
            $requestURI = str_replace($matches[0], '', $requestURI);
        }

        foreach ($routes as $routeOptions) {
            if (preg_match($routeOptions['regex'], $requestURI, $matches)) {
                array_shift($matches); // remove first capture group match

                foreach ($matches as $idx => $part) {
                    $params[$routeOptions['params'][$idx]] = $part;
                }

                return $this->handle($routeOptions['handler'], $params, $responseType);
            }
        }

        return false;
    }
}
