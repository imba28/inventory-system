<?php


namespace App\DependencyInjection\Compiler;


use App\Bootstrap\Bootstrap;
use App\Database\DatabaseInterface;
use App\Database\MySQL;
use App\Database\SQLite;
use App\DependencyInjection\AppExtension;
use App\DependencyInjection\Configuration;
use App\Models\Model;
use App\QueryBuilder\Builder;
use App\QueryBuilder\SQLiteBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DatabasePass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $container->getExtensionConfig('app'));

        $mysqlDefinition = $container->getDefinition(MySQL::class);
        $mysqlDefinition->setArgument('$host', $config['database']['host']);
        $mysqlDefinition->setArgument('$database', $config['database']['database']);
        $mysqlDefinition->setArgument('$user', $config['database']['user']);
        $mysqlDefinition->setArgument('$password', $config['database']['password']);
        $mysqlDefinition->setArgument('$port', $config['database']['port']);

        $bootstrapDefinition = $container->getDefinition(Bootstrap::class);
        $bootstrapDefinition->setPublic(true);

        if ($config['database']['driver'] === 'mysql') {
            $databaseDefinition = $mysqlDefinition;
        } else {
            $databaseDefinition = $container->getDefinition(SQLite::class);
        }
        $container->setDefinition(DatabaseInterface::class, $databaseDefinition);
        $container->getDefinition(Builder::class)->addMethodCall('setTablePrefix', [$config['database']['table_prefix']]);
    }

    private function processConfiguration(ConfigurationInterface $configuration, array $configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}
