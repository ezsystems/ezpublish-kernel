<?php
/**
 * File containing the ContentTypeGroup class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeGroupStub extends ContentTypeGroup
{
    private $names;

    private $descriptions;

    public function __construct( array $values = array() )
    {
        $this->names = new \ArrayObject(
            ( isset( $values['names'] ) ? $values['names'] : array() )
        );
        unset( $values['names'] );

        $this->descriptions = new \ArrayObject(
            ( isset( $values['descriptions'] ) ? $values['descriptions'] : array() )
        );
        unset( $values['descriptions'] );

        foreach ( $values as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
    }

    public function __get( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'names':
            case 'descriptions':
                return $this->$propertyName;

            case 'name':
                return $this->getName();

            case 'description':
                return $this->getDescription();
        }
        return parent::__get( $propertyName );
    }

    public function __isset( $propertyName )
    {
        switch( $propertyName )
        {
            case 'names':
            case 'name':
            case 'descriptions':
            case 'description':
                return true;
        }
        return parent::__isset( $propertyName );
    }

    /**
     * 5.x only
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
    public function getNames()
    {
        return $this->names->getArrayCopy();
    }

    /**
     * 5.x only
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none existis.
     */
    public function getName( $languageCode )
    {
        return $this->names[$this>mainLanguageCode];
    }

    /**
     *  5.x only
     * This method returns the human readable description of the content type
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions->getArrayCopy();
    }

    /**
     * 5.x only
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none existis.
     */
    public function getDescription( $languageCode )
    {
        return $this->descriptions[$this>mainLanguageCode];
    }
}
