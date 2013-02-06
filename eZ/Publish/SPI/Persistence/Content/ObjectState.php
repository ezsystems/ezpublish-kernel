<?php

/**
 * File containing the ObjectState class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a persistent object state
 */
class ObjectState extends ValueObject
{
    /**
     * The id of the object state
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
     * Human readable name of the object state
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
     * Human readable description of the object state
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
