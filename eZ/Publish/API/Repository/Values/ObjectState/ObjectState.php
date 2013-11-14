<?php
/**
 * File containing the ObjectState class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\MultiLanguageValueBase;

/**
 * This class represents a object state value
 *
 * @property-read mixed $id the id of the object state
 * @property-read int $priority the priority in the group ordering
 * @property-read string $defaultLanguageCode the default language of the object state names and description used for fallback.
 * @property-read string[] $languageCodes the available languages
 */
abstract class ObjectState extends MultiLanguageValueBase
{
    /**
     * Primary key
     *
     * @var mixed
     */
    protected $id;

    /**
     * Priority for ordering
     *
     * @var int
     */
    protected $priority;

    /**
     * @deprecated use mainLanguageCode in the base class
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

    /**
     * The object state group this object state belongs to
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    abstract public function getObjectStateGroup();

}
