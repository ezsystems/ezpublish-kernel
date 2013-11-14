<?php
/**
 * File containing the ObjectStateCreateStruct class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\MultiLanguageCreateStructBase;

/**
 * This class represents a value for creating object states
 *
 */
class ObjectStateCreateStruct extends MultiLanguageCreateStructBase
{
    /**
     * Priority for ordering. If not set the object state is created as the last one.
     *
     * @var int
     */
    public $priority = false;

    /**
     * The default language code
     *
     * @deprected use mainLanguageCode in base class instead
     *
     * @var string
     */
    public $defaultLanguageCode;

}
