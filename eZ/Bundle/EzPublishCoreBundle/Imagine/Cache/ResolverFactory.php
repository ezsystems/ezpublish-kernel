<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Cache;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class ResolverFactory
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $resolver;

    /** @var string|null */
    private $resolverDecoratorClass;

    /** @var string */
    private $proxyResolverClass;

    /** @var string */
    private $relativeResolverClass;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface $resolver
     * @param string $proxyResolverClass
     * @param string $relativeResolverClass
     */
    public function __construct(
        ConfigResolverInterface $configResolver,
        ResolverInterface $resolver,
        $proxyResolverClass,
        $relativeResolverClass
    ) {
        $this->configResolver = $configResolver;
        $this->resolver = $resolver;
        $this->proxyResolverClass = $proxyResolverClass;
        $this->relativeResolverClass = $relativeResolverClass;
    }

    /**
     * @return \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface
     */
    public function createCacheResolver()
    {
        $imageHost = $this->configResolver->hasParameter('image_host') ?
            $this->configResolver->getParameter('image_host') :
            '';

        if ($imageHost === '') {
            return $this->resolver;
        }

        if ($imageHost === '/') {
            $this->resolverDecoratorClass = $this->relativeResolverClass;
        } else {
            $this->resolverDecoratorClass = $this->proxyResolverClass;
        }

        return new $this->resolverDecoratorClass($this->resolver, [$imageHost]);
    }
}
