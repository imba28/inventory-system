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

    public function addRoute($request_method, $path, $handler)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->addRoute($request_method, $p, $handler);
            }
            return;
        }

        $request_method = strtoupper($request_method);

        if (in_array($request_method, array_keys($this->routes))) {
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
                        $routeOptions['regex'][] = '([\p{N}\p{L}\s\-,]+)'; // matches any unicode character, whitespace, minus and comma.
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

            $this->routes[$request_method][self::$routeCount++] = $routeOptions;
        } else {
            throw new \InvalidArgumentException("{$request_method} is not a valid http request type!");
        }
    }

    public function handle($handle, $params = array(), $response_type = 'html')
    {
        if ($handle instanceof \Closure) {
            return $handle($params);
        } elseif (is_string($handle)) {
            $split = explode('#', $handle);

            $controller_class = "\App\Controller\\{$split[0]}";
            $controller_action = $split[1];

            $controller = new $controller_class($response_type);

            if (is_callable(array($controller, $controller_action), true)) {
                $controller->handle($controller_action, $params);
            } else {
                $controller->error(501, "{$controller_class} does not provide {$controller_action}()!");
            }
        } else {
            throw new \InvalidArgumentException('invalid handle!');
        }
    }

    public function route($request_uri = null, $request_method = null)
    {
        if (is_null($request_uri)) {
            $request_uri = urldecode($_SERVER['REQUEST_URI']);
        }
        if (is_null($request_method)) {
            $request_method = $_SERVER['REQUEST_METHOD'];
        }

        $uri = ltrim(parse_url($request_uri, PHP_URL_PATH), '/');
        $params = parse_url($request_uri, PHP_URL_QUERY);

        $routes = $this->routes[$request_method] + $this->routes['ALL'];
        ksort($routes);

        $this->findHandler($routes, $request_uri);

        throw new \ErrorException("Route `{$uri}`not defined!");
    }

    private function findHandler(array $routes, $request_uri)
    {
        $params = array();
        $response_type = 'html';

        if (preg_match('/.(json|html|xml)$/', $request_uri, $matches)) {
            $response_type = $matches[1];
            $request_uri = str_replace($matches[0], '', $request_uri);
        }

        foreach ($routes as $path => $routeOptions) {
            if (preg_match($routeOptions['regex'], $request_uri, $matches)) {
                array_shift($matches); // remove first capture group match

                foreach ($matches as $idx => $part) {
                    $params[$routeOptions['params'][$idx]] = $part;
                }

                return $this->handle($routeOptions['handler'], $params, $response_type);
            }
        }

        return false;
    }
}
