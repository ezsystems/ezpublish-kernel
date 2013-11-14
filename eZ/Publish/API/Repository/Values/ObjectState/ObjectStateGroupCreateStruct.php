<?php
/**
 * File containing the ObjectStateGroupCreateStruct class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\MultiLanguageCreateStructBase;

/**
 * This class represents a value for creating object state groups
 */
class ObjectStateGroupCreateStruct extends MultiLanguageCreateStructBase
{

    /**
     * The default language code
     *
     * @deprecated use mainLanguageCode in base class instead
     *
     * @var string
     */
    public $defaultLanguageCode;

}
