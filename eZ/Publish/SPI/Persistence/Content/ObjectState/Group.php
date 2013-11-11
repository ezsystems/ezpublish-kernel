<?php

/**
 * File containing the ObjectStateGroup class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\ObjectState;

use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a persistent object state group
 */
class Group extends MultiLanguageValueBase
{
    /**
     * The id of the object state group
     *
     * @var mixed
     */
    public $id;
}
