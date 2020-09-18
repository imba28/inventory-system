<?php

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    private $loaded = false;

    private $router;

    private $kernel;

    public function __construct(Router $router, KernelInterface $kernel)
    {
        $this->router = $router;
        $this->kernel = $kernel;
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "legacy" loader twice');
        }

        $this->loaded = true;

        $router = $this->router;
        include $this->kernel->getProjectDir() . '/src/config/legacy_routes.php';

        $routeCollection = new RouteCollection();

        foreach ($router->getRoutes() as $method => $routes) {
            foreach ($routes as $routeInfo) {
                $name = $routeInfo['handler'];
                $path = $routeInfo['path'];

                $methods = $method === 'ALL' ? ['GET', 'POST', 'PUT', 'DELETE'] : [$method];
                $requirements = [];
                $defaults = [
                    '_controller' => 'App\Controllers\\'. str_replace('#', '::', $routeInfo['handler']),
                    '_format' => 'html'
                ];

                if (strpos($path, ':') !== false) {
                    foreach (explode('/', $path) as $part) {
                        if (!isset($part[0])) {
                            continue;
                        }

                        if ($part[0] === ':') {
                            $partOriginal = $part;
                            if ($part[1] === '_') {
                                $part = str_replace('_', '', $part);
                                $requirements[substr($part, 1)] = '\d+';
                            }
                            $path = str_replace($partOriginal,'{'. substr($part, 1) .'}', $path);
                        }
                    }
                }

                $path .= '.{_format}';

                $route = new Route($path, $defaults, $requirements, [], '', [], $methods);
                $routeCollection->add('legacy_' . $name . '_' . $path, $route);
            }
        }

        return $routeCollection;
    }

    public function supports($resource, string $type = null)
    {
        return 'legacy' === $type;
    }
}
