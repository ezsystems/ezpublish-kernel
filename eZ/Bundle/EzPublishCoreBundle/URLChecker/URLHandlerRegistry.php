<?php

namespace eZ\Bundle\EzPublishCoreBundle\URLChecker;

use InvalidArgumentException;

class URLHandlerRegistry implements URLHandlerRegistryInterface
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLHandlerInterface[]
     */
    private $handlers = [];

    /**
     * URLHandlerRegistry constructor.
     */
    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function addHandler($scheme, URLHandlerInterface $handler)
    {
        $this->handlers[$scheme] = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function supported($scheme)
    {
        return isset($this->handlers[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler($scheme)
    {
        if (!$this->supported($scheme)) {
            throw new InvalidArgumentException("Unsupported URL scheme: $scheme");
        }

        return $this->handlers[$scheme];
    }
}
