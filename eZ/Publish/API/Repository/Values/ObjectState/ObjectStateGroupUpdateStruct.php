<?php
/**
 * File containing the ObjectStateGroupUpdateStruct class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\MultiLanguageUpdateStructBase;

/**
 * This class represents a value for updating object state groups
 */
class ObjectStateGroupUpdateStruct extends MultiLanguageUpdateStructBase
{
    /**
     * @deprecated use mainLanguageCode in base class instead
     *
     * The default language code
     *
     * @var string
     */
    public $defaultLanguageCode;

}
