<?php

/**
 * File containing the ObjectStateGroup class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\ObjectState;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a persistent object state group
 */
class Group extends ValueObject
{
    /**
     * The id of the object state group
     *
     * @var mixed
     */
    public $id;

    /**
     * The identifier for the object state group
     *
     * @var string
     */
    public $identifier;

    /**
     * The default language code for
     *
     * @var string
     */
    public $defaultLanguage;

    /**
     * The available language codes for names an descriptions
     *
     * @var string[]
     */
    public $languageCodes;

    /**
     * Human readable name of the object state group
     *
     * The structure of this field is:
     * <code>
     * array( 'eng-US' => '<name_eng>', 'ger-DE' => '<name_de>' );
     * </code>
     *
     * @var string[]
     */
    public $name;

    /**
     * Human readable description of the object state group
     *
     * The structure of this field is:
     * <code>
     * array( 'eng-US' => '<description_eng>', 'ger-DE' => '<description_de>' );
     * </code>
     *
     * @var string[]
     */
    public $description;

}
