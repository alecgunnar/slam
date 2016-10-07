<?php

namespace Slam;

use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Interop\Container\ContainerInterface as InteropContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

class ContainerAdapter implements InteropContainerInterface
{
    /**
     * @var SymfonyContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    const SERVICE_NOT_FOUND_MESSAGE = 'The service "%s" was not found in the container.';

    /**
     * @var string
     */
    const COULD_NOT_RETRIEVE_MESSAGE = 'The service "%s" could not be retrieved because: %s';

    /**
     * @param SymfonyContainerInterface $container
     */
    public function __construct(SymfonyContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        try {
            return $this->container->get($id);
        } catch(ServiceCircularReferenceException $e) {
            $msg = sprintf(self::COULD_NOT_RETRIEVE_MESSAGE, $id, $e->getMessage());
            throw new ContainerException($msg);
        } catch(ServiceNotFoundException $e) {
            if ($this->container->hasParameter($id)) {
                return $this->container->getParameter($id);
            }

            $msg = sprintf(self::SERVICE_NOT_FOUND_MESSAGE, $id);
            throw new NotFoundException($msg);
        }
    }

    public function has($id)
    {
        return $this->container->has($id);
    }
}
