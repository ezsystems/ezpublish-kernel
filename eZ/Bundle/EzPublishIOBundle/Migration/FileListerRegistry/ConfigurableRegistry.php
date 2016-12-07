<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\FileListerRegistry;

use eZ\Bundle\EzPublishIOBundle\Migration\FileListerRegistry;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * A registry of FileListerInterfaces which is configurable via the array passed to its constructor.
 */
final class ConfigurableRegistry implements FileListerRegistry
{
    /** @var \eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface[] */
    private $registry = [];

    /**
     * @param \eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface[] $items Hash of FileListerInterfaces, with identifier string as key.
     */
    public function __construct(array $items = [])
    {
        $this->registry = $items;
    }

    /**
     * Returns the FileListerInterface matching the argument.
     *
     * @param string $identifier An identifier string.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no FileListerInterface exists with this identifier
     *
     * @return \eZ\Bundle\EzPublishIOBundle\Migration\FileListerInterface The FileListerInterface given by the identifier.
     */
    public function getItem($identifier)
    {
        if (isset($this->registry[$identifier])) {
            return $this->registry[$identifier];
        }

        throw new NotFoundException('Migration file lister', $identifier);
    }

    /**
     * Returns the identifiers of all registered FileListerInterfaces.
     *
     * @return string[] Array of identifier strings.
     */
    public function getIdentifiers()
    {
        return array_keys($this->registry);
    }
}
