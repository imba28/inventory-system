<?php
namespace App;

class Router {
    use \App\Traits\Singleton;

    protected $routes = array(
        'ALL' => array(),
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array()
    );

    public function addRoute($request_method, $path, $handler) {
        if(is_array($path)) {
            foreach($path as $p) {
                $this->addRoute($request_method, $p, $handler);
            }
            return;
        }

        $request_method = strtoupper($request_method);

        if(in_array($request_method, array_keys($this->routes))) {
            $routeOptions = array(
                'regex' => array(),
                'params' => array(),
                'handler' => $handler
            );

            $parts = explode('/', $path);
            foreach($parts as $part) {
                if(isset($part[0]) && $part[0] === ':') { // dynamic part
                    $routeOptions['regex'][] = '([a-zA-Z0-9\-]+)';
                    $routeOptions['params'][] = substr($part, 1);
                }
                else { // static part
                    if($part === '*') $part = '(.+)';
                    $routeOptions['regex'][] = $part;
                }
            }

            $routeOptions['regex'] = '^' . implode('\/', $routeOptions['regex']) . '$';

            $this->routes[$request_method][$path] = $routeOptions;
        }
        else throw new \InvalidArgumentException("{$request_method} is not a valid http request type!");
    }

    public function handle($handle, $params = array()) {
        if($handle instanceof \Closure) {
            return $handle($params);
        }
        elseif(is_string($handle)) {
            $split = explode('#', $handle);

            $controller_class = "\App\Controller\\{$split[0]}";
            $controller_action = $split[1];

            $controller = new $controller_class();
            $controller->$controller_action($params);
        }
        else throw new \InvalidArgumentException('invalid handle!');
    }

    public function route() {
        $request_uri = $_SERVER['REQUEST_URI'];
        $request_method = $_SERVER['REQUEST_METHOD'];
        $uri = ltrim(parse_url($request_uri, PHP_URL_PATH), '/');
        $params = parse_url($request_uri, PHP_URL_QUERY);

        /*if(preg_match('/^\/api\/(v[0-9]+)\//', $uri, $match)) {
            $version = intval(preg_replace('/[\D]/', '', $match[1]));
            if(empty($version)) {
                throw new \InvalidArgumentException('Invalid API Version!');
            }*/
            $uri = preg_replace('/^\/api\/(v[0-9]+)\//', '', $uri);
            if(isset($this->routes[$request_method][$uri])) {
                $handler = $this->routes[$request_method][$uri];
                return $this->handle($handler);
            }
            /*elseif(isset($this->routes['ALL'][$uri])) {
                $handler = $this->routes['ALL'][$uri];
                return $this->handle($handler);
            }*/
            else {
                foreach(array_merge($this->routes['ALL'], $this->routes[$request_method]) as $path => $routeOptions) {
                    if(preg_match("/{$routeOptions['regex']}/", $request_uri, $matches)) {
                        $params = array();
                        array_shift($matches); // remove first capture group match


                        foreach($matches as $idx => $part) {
                            $params[$routeOptions['params'][$idx]] = $part;
                        }

                        return $this->handle($routeOptions['handler'], $params);
                    }
                    if(preg_match('/^p\:\((.+)\)$/i', $path, $m)) {
                        $pattern = '/('.str_replace('/', '\/', $m[1]).')/';
                        if(preg_match($pattern, $request_uri)) {
                            return $this->handle($routeOptions['handler']);
                        }
                    }
                }
                throw new \ErrorException("Route `{$uri}`not defined!");
            }
            // $exp = explode('/', $uri);
            //
            // if(count($exp) > 1 && count($exp) % 2 != 0) throw new \InvalidArgumentException('Arguments do not match!');
            //
            // for($i = 0; $i < count($exp); $i += 2) {
            //     $a = $exp[$i];
            //     if(count($exp) !== 1) $b = $exp[$i+1];
            //
            //     if(!isset($query)) {
            //         $query = new App\QueryBuilder\Builder($a);
            //     }
            //     if(isset($b)) {
            //         $query->where('id', '=', $b)
            //     }
            // }
        /*}
        else throw new \InvalidArgumentException('Invalid API Version!');*/
    }
}
?>