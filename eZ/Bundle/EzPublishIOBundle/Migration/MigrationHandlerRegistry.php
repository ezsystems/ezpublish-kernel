<?php

/**
 * File containing the MigrationHandlerRegistry interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Migration;

/**
 * A registry of MigrationHandlerInterfaces.
 */
interface MigrationHandlerRegistry
{
    /**
     * @param \eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface[] $items Hash of MigrationHandlerInterfaces, with identifier string as key.
     */
    public function __construct(array $items = []);

    /**
     * Returns the MigrationHandlerInterface matching the argument.
     *
     * @param string $identifier An identifier string.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no MigrationHandlerInterface exists with this identifier
     *
     * @return \eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface The MigrationHandlerInterface given by the identifier.
     */
    public function getItem($identifier);

    /**
     * Returns the identifiers of all registered MigrationHandlerInterfaces.
     *
     * @return string[] Array of identifier strings.
     */
    public function getIdentifiers();
}
