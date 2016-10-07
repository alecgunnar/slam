<?php

namespace Slam;

use Slim\App;
use Slim\CallableResolver;
use Slim\Http\Environment;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Interop\Container\ContainerInterface as InteropContainerInterface;
use Slam\Exception\ConfigurationException;
use Psr\Http\Message\ResponseInterface;

class Application
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @param string[] $dirs
     * @param string $configFile=null
     */
    public function __construct(array $dirs, string $configFile=null)
    {
        $dirs = $this->checkDirs($dirs);

        $builder = new ContainerBuilder();
        $container = new ContainerAdapter($builder);

        $this->addDirs($builder, $dirs);
        $this->loadServices($builder, $dirs['config_dir'], $configFile);
        $this->buildSynthetics($builder, $container);

        $this->app = new App($container);
    }

    /**
     * @return ResponseInterface
     */
    public function __invoke(): ResponseInterface
    {
        return $this->app->run();
    }

    protected function checkDirs(array $dirs): array
    {
        if (!isset($dirs['root_dir'])) {
            throw new ConfigurationException('You must at least supply the "root_dir" path.');
        }

        return $dirs + [
            'config_dir' => $dirs['root_dir'] . '/config',
            'web_dir' => $dirs['root_dir'] . '/web'
        ];
    }

    protected function addDirs(SymfonyContainerInterface $builder, array $dirs)
    {
        foreach ($dirs as $name => $dir) {
            $builder->setParameter($name, $dir);
        }
    }

    protected function loadServices(
        SymfonyContainerInterface $builder,
        string $dir,
        string $file = null
    ) {
        $loader = new YamlFileLoader(
            $builder,
            new FileLocator($dir)
        );

        $loader->load($file ?? 'app.yml');
    }

    protected function buildSynthetics(
        SymfonyContainerInterface $builder,
        InteropContainerInterface $container
    ) {
        $settings = [
            'httpVersion' => $builder->getParameter('httpVersion'),
            'responseChunkSize' => $builder->getParameter('responseChunkSize'),
            'outputBuffering' => $builder->getParameter('outputBuffering'),
            'determineRouteBeforeAppMiddleware' => $builder->getParameter('determineRouteBeforeAppMiddleware'),
            'displayErrorDetails' => $builder->getParameter('displayErrorDetails'),
            'addContentLengthHeader' => $builder->getParameter('addContentLengthHeader'),
            'routerCacheFile' => $builder->getParameter('routerCacheFile')
        ];

        $builder->set('settings', $settings);

        $builder->set(
            'environment',
            new Environment($_SERVER)
        );

        $builder->set(
            'callableResolver',
            new CallableResolver($container)
        );
    }
}
