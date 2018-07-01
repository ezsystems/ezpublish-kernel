<?php

/**
 * File containing the Type Update Handler base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Update;

use eZ\Publish\SPI\Persistence\Content\Type;

/**
 * Base class for update handlers.
 */
abstract class Handler
{
    /**
     * Updates existing content objects from $fromType to $toType.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $fromType
     * @param \eZ\Publish\SPI\Persistence\Content\Type $toType
     */
    abstract public function updateContentObjects(Type $fromType, Type $toType);

    /**
     * Deletes $fromType and all of its field definitions.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $fromType
     */
    abstract public function deleteOldType(Type $fromType);

    /**
     * Publishes $toType to $newStatus.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $toType
     * @param int $newStatus
     */
    abstract public function publishNewType(Type $toType, $newStatus);
}
