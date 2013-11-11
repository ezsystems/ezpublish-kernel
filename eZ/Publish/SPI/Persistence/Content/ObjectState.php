<?php

/**
 * File containing the ObjectState class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a persistent object state
 */
class ObjectState extends MultiLanguageValueBase
{
    /**
     * The id of the object state
     *
     * @var mixed
     */
    public $id;

    /**
     * The id of the group this object state belongs to
     *
     * @var mixed
     */
    public $groupId;

    /**
     * The priority of the object state in the group
     *
     * @var int
     */
    public $priority;
}
