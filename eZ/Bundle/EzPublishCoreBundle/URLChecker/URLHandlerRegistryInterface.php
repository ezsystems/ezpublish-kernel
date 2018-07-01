<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\URLChecker;

interface URLHandlerRegistryInterface
{
    /**
     * Adds scheme handler.
     *
     * @param string $scheme
     * @param \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLHandlerInterface $handler
     */
    public function addHandler($scheme, URLHandlerInterface $handler);

    /**
     * Is scheme supported ?
     *
     * @param string $scheme
     * @return bool
     */
    public function supported($scheme);

    /**
     * Returns handler for scheme.
     *
     * @param string $scheme
     * @return \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLHandlerInterface
     *
     * @throw \InvalidArgumentException When scheme isn't supported
     */
    public function getHandler($scheme);
}
