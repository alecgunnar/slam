<?php

namespace Slam\Configurator;

use Slim\Interfaces\RouterInterface;
use Slim\Interfaces\CallableResolverInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Slam\Exception\ConfigurationException;

class RouterConfigurator
{
    /**
     * @var CallableResolverInterface
     */
    protected $resolver;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @param string $configFile
     */
    public function __construct(CallableResolverInterface $resolver, string $configFile)
    {
        $this->resolver = $resolver;
        $this->configFile = $configFile;
    }

    public function configure(RouterInterface $router)
    {
        try {
            $routes = Yaml::parse(file_get_contents($this->configFile));
        } catch (ParseException $e) {
            $msg = sprintf('Could not load routes because: %s', $e->getMessage());
            throw new ConfigurationException($msg);
        }

        foreach ($routes as $name => $route) {
            $this->checkRoute($name, $route);

            if (isset($route['methods']) && !is_array($route['methods'])) {
                $route['methods'] = [$route['methods']];
            }

            $router->map(
                $route['methods'] ?? ($route['method'] ?? ['GET']),
                $route['path'],
                $this->resolver->resolve($route['handler'])
            );
        }
    }

    protected function checkRoute(string $name, array $route)
    {
        if (!isset($route['path'])) {
            $msg = sprintf('Missing "path" parameter for route: %s', $name ?? 'not named');
            throw new ConfigurationException($msg);
        }

        if (!isset($route['handler'])) {
            $msg = sprintf('Missing "handler" parameter for route: %s', $name ?? 'not named');
            throw new ConfigurationException($msg);
        }
    }
}
