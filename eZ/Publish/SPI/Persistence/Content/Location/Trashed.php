<?php

/**
 * File containing the TrashedLocation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Location;

use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Struct containing accessible properties on TrashedLocation entities.
 */
class Trashed extends Location
{
    /**
     * Trashed timestamp.
     *
     * @var mixed Trashed timestamp.
     */
    public $trashed;
}
