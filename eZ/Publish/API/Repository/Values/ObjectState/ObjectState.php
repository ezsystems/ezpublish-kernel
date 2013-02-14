<?php
/**
 * File containing the ObjectState class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a object state value
 *
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read int $priority the priority in the group ordering
 * @property-read string $defaultLanguageCode the default language of the object state group names and description used for fallback.
 * @property-read string[] $languageCodes the available languages
 */
abstract class ObjectState extends ValueObject
{
    /**
     * Primary key
     *
     * @var mixed
     */
    protected $id;

    /**
     * Readable string identifier of the object state
     *
     * @var string
     */
    protected $identifier;

    /**
     * Priority for ordering
     *
     * @var int
     */
    protected $priority;

    /**
     * The default language code
     *
     * @var string
     */
    protected $defaultLanguageCode;

    /**
     * The available language codes for names an descriptions
     *
     * @var string[]
     */
    protected $languageCodes;

    /**
     * This method returns the human readable name in all provided languages
     * of the content type
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getNames();

    /**
     * This method returns the name of the content type in the given language
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    abstract public function getName( $languageCode );

    /**
     * This method returns the human readable description of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getDescriptions();

    /**
     * This method returns the name of the content type in the given language
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    abstract public function getDescription( $languageCode );

    /**
     * The object state group this object state belongs to
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    abstract public function getObjectStateGroup();

}
