<?php

/**
 * File containing the IdentifierBased class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerRegistry;

use eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerRegistry;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * A registry of MigrationHandlerInterfaces that uses an identifier string to identify the handler.
 */
class IdentifierBased implements MigrationHandlerRegistry
{
    /** @var \eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface[] */
    private $registry = [];

    /**
     * @param \eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface[] $items Hash of MigrationHandlerInterfaces, with identifier string as key.
     */
    public function __construct(array $items = [])
    {
        $this->registry = $items;
    }

    /**
     * Returns the MigrationHandlerInterface matching the argument.
     *
     * @param string $identifier An identifier string.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no MigrationHandlerInterface exists with this identifier
     *
     * @return \eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface The MigrationHandlerInterface given by the identifier.
     */
    public function getItem($identifier)
    {
        if (isset($this->registry[$identifier])) {
            return $this->registry[$identifier];
        }

        throw new NotFoundException('Migration handler', $identifier);
    }

    /**
     * Returns the identifiers of all registered MigrationHandlerInterfaces.
     *
     * @return string[] Array of identifier strings.
     */
    public function getIdentifiers()
    {
        return array_keys($this->registry);
    }
}
