<?php
/**
 * File containing the ContentTypeGroupStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class ContentTypeGroupStub extends ContentTypeGroup
{
    protected $names;

    protected $descriptions;

    function __construct( array $data = array() )
    {
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
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
        return $this->names;
    }

    /**
     * 5.x only
     * This method returns the name of the content type in the given language
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none existis.
     */
    public function getName( $languageCode )
    {
        return $this->names[$languageCode];
    }

    /**
     * 5.x only
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
        return $this->descriptions;
    }

    /**
     * 5.x only
     * This method returns the name of the content type in the given language
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none existis.
     */
    public function getDescription( $languageCode )
    {
        return $this->descriptions[$languageCode];
    }
}
