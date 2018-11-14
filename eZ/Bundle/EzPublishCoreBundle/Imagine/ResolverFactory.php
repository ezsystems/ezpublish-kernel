<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class ResolverFactory
{
    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * @var string
     */
    private $proxyResolverClass;

    /**
     * @var array
     */
    private $hosts = [];

    public function __construct(ConfigResolverInterface $configResolver, ResolverInterface $resolver, $proxyResolverClass)
    {
        $this->resolver = $resolver;
        $this->proxyResolverClass = $proxyResolverClass;

        if ($configResolver->hasParameter('image_host') &&
            ($imageHost = $configResolver->getParameter('image_host')) !== '') {
            $this->hosts = [$imageHost];
        }
    }

    public function createCacheResolver()
    {
        return new $this->proxyResolverClass($this->resolver, $this->hosts);
    }
}
