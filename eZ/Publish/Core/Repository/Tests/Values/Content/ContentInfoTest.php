<?php
/**
 * File containing the ContentInfoTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class ContentInfoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\API\Repository\Values\Content\ContentInfo::getProperties
     */
    public function testObjectProperties()
    {
        $object = new ContentInfo;
        $properties = $object->attributes();
        self::assertNotContains( 'internalFields', $properties, 'Internal property found ' );
        self::assertContains( 'contentTypeId', $properties, 'Property not found' );
        self::assertContains( 'id', $properties, 'Property not found' );
        self::assertContains( 'name', $properties, 'Property not found' );
        self::assertContains( 'sectionId', $properties, 'Property not found' );
        self::assertContains( 'currentVersionNo', $properties, 'Property not found' );
        self::assertContains( 'published', $properties, 'Property not found' );
        self::assertContains( 'ownerId', $properties, 'Property not found' );
        self::assertContains( 'modificationDate', $properties, 'Property not found' );
        self::assertContains( 'publishedDate', $properties, 'Property not found' );
        self::assertContains( 'alwaysAvailable', $properties, 'Property not found' );
        self::assertContains( 'remoteId', $properties, 'Property not found' );
        self::assertContains( 'mainLanguageCode', $properties, 'Property not found' );
        self::assertContains( 'mainLocationId', $properties, 'Property not found' );

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
