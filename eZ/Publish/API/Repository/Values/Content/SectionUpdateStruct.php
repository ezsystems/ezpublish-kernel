<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to provide data for updating a section. At least one property has to set.
 */
class SectionUpdateStruct extends ValueObject
{
    /**
     * If set the Unique identifier of the section is changes
     *
     * Needs to be a unique Section->identifier string value.
     *
     * @var string
     */
    public $identifier;

    /**
     * If set the name of the section is changed
     *
     * @var string
     */
    public $name;
}
