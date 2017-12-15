<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\URLChecker;

interface URLHandlerInterface
{
    /**
     * Validates given list of URLs.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL[] $urls
     */
    public function validate(array $urls);

    /**
     * Set handler options.
     *
     * @param array|null $options
     */
    public function setOptions(array $options = null);
}
