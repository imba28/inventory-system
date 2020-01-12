<?php

namespace App;

use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle()
        ];
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $configPath = $this->getProjectDir() . '/src/config/config.yml';
        if (file_exists($this->getProjectDir() . '/src/config/config_' . $this->getEnvironment() . '.yml')) {
            $configPath = $this->getProjectDir() . '/src/config/config_' . $this->getEnvironment() . '.yml';
        }
        $loader->load($configPath, 'yaml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getProjectDir().'/src/config/routing.yml');
    }
}
