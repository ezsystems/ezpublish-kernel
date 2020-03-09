<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Update;

use eZ\Publish\SPI\Persistence\Content\Type;

/**
 * Base class for update handlers.
 *
 * @internal For internal use by Repository.
 */
abstract class Handler
{
    /**
     * Update existing Content items from one version of a Content Type to another one.
     */
    abstract public function updateContentObjects(Type $fromType, Type $toType): void;

    /**
     * Delete old version of a Content Type and all of its Field Definitions.
     */
    abstract public function deleteOldType(Type $fromType): void;

    /**
     * Change Content Type status.
     */
    abstract public function publishNewType(Type $toType, int $newStatus): void;
}
