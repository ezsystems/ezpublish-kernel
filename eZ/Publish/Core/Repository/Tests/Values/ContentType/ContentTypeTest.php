<?php
/**
 * File containing the ContentTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\ContentType;

use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class ContentTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\ContentType::getProperties
     */
    public function testObjectProperties()
    {
        $object = new ContentType( array( 'fieldDefinitions' => array() ) );
        $properties = $object->attributes();
        self::assertNotContains( 'internalFields', $properties, 'Internal property found ' );
        self::assertContains( 'contentTypeGroups', $properties, 'Property not found' );
        self::assertContains( 'fieldDefinitions', $properties, 'Property not found' );
        self::assertContains( 'id', $properties, 'Property not found' );
        self::assertContains( 'status', $properties, 'Property not found' );
        self::assertContains( 'identifier', $properties, 'Property not found' );
        self::assertContains( 'creationDate', $properties, 'Property not found' );
        self::assertContains( 'modificationDate', $properties, 'Property not found' );
        self::assertContains( 'creatorId', $properties, 'Property not found' );
        self::assertContains( 'modifierId', $properties, 'Property not found' );
        self::assertContains( 'remoteId', $properties, 'Property not found' );
        self::assertContains( 'urlAliasSchema', $properties, 'Property not found' );
        self::assertContains( 'nameSchema', $properties, 'Property not found' );
        self::assertContains( 'isContainer', $properties, 'Property not found' );
        self::assertContains( 'mainLanguageCode', $properties, 'Property not found' );
        self::assertContains( 'defaultAlwaysAvailable', $properties, 'Property not found' );
        self::assertContains( 'defaultSortField', $properties, 'Property not found' );
        self::assertContains( 'defaultSortOrder', $properties, 'Property not found' );

        // check for duplicates and double check existence of property
        $propertiesHash = array();
        foreach ( $properties as $property )
        {
            if ( isset( $propertiesHash[$property] ) )
            {
                self::fail( "Property '{$property}' exists several times in properties list" );
            }
            else if ( !isset( $object->$property ) )
            {
                self::fail( "Property '{$property}' does not exist on object, even though it was hinted to be there" );
            }
            $propertiesHash[$property] = 1;
        }
    }
}
