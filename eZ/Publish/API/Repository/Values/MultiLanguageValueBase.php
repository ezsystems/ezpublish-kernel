<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\MultiLanguageValueBase class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\API\Repository\Values;

/**
 * This is the base class for all classes providing an identifier and multi language names and description
 *
 * @property-read string $identifier the identifier of the domain object
 * @property-read string $mainLanguageCode the main language code
 *
 * @package eZ\Publish\API\Repository\Values
 */
abstract class MultiLanguageValueBase extends ValueObject
{
    /**
     * This method returns the human readable name in all provided languages
     * of the domain object
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
     * This method returns the name of the domain object in the given language
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    abstract public function getName( $languageCode );

    /**
     * This method returns the human readable description of the domain object
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
     * This method returns the name of the domain object in the given language
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    abstract public function getDescription( $languageCode );

    /**
     * String identifier of the domain object
     *
     * @var string
     */
    protected $identifier;

    /**
     * the main language code used for fallbacks
     *
     * @var string
     */
    protected $mainLanguageCode;

}

