<?php

namespace App;

use App\DependencyInjection\AppExtension;
use App\DependencyInjection\Compiler\DatabasePass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new DoctrineMigrationsBundle(),
            new SecurityBundle()
        ];

        if ($this->getEnvironment() == 'dev') {
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new MakerBundle();
        }

        return $bundles;
    }

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DatabasePass());
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->registerExtension(new AppExtension());

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
