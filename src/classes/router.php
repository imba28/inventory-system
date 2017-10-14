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
        $request_method = strtoupper($request_method);
        if(in_array($request_method, array_keys($this->routes))) {
            $this->routes[$request_method][$path] = $handler;
        }
        else throw new \InvalidArgumentException("{$request_method} is not a valid http request type!");
    }

    public function handle($handle) {
        if($handle instanceof \Closure) {
            return $handle();
        }
        else if(is_array($handle) && $handle[0] == 'Controller' && count($handle) == 3) {
            $controller_name = "\App\Controller\\".$handle[1];
            $controller_action = $handle[2];

            $controller = new $controller_name();
            $controller->$controller_action();
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
            elseif(isset($this->routes['ALL'][$uri])) {
                $handler = $this->routes['ALL'][$uri];
                return $this->handle($handler);
            }
            else {
                foreach(array_merge($this->routes['ALL'], $this->routes[$request_method]) as $path => $value) {
                    if(preg_match('/^p\:\((.+)\)$/i', $path, $m)) {
                        $pattern = '/('.str_replace('/', '\/', $m[1]).')/';
                        if(preg_match($pattern, $request_uri)) {
                            return $this->handle($value);
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