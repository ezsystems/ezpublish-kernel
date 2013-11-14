<?php
/**
 * File containing the ObjectStateGroup class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\MultiLanguageValueBase;

/**
 * This class represents an object state group value
 *
 * @property-read mixed $id the id of the object state group
 * @property-read string $defaultLanguageCode, the default language code of the object state group names and description used for fallback.
 * @property-read string[] $languageCodes the available languages
 */
abstract class ObjectStateGroup extends MultiLanguageValueBase
{
    /**
     * Primary key
     *
     * @var mixed
     */
    protected $id;

    /**
     * @deprecated use mainLanguageCode in base class instead
     *
     * The default language code
     *
     * @var string
     */
    protected $defaultLanguageCode;

    /**
     * @deprecated
     *
     * The available language codes for names an descriptions
     *
     * @var string[]
     */
    protected $languageCodes;

}
