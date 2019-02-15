<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class RelativeResolver implements ResolverInterface
{
    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * @param \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        return $this->resolver->isStored($path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return $this->rewriteUrl($this->resolver->resolve($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        return $this->resolver->store($binary, $path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        return $this->resolver->remove($paths, $filters);
    }

    /**
     * Returns relative image path.
     *
     * @param $url string
     * @return string
     */
    protected function rewriteUrl($url)
    {
        return parse_url($url, PHP_URL_PATH);
    }
}
