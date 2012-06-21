<?php
/**
 * File containing the ContentTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\Content;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

/**
 *
 */
class ContentInfoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\Content\ContentInfo::getIterator
     * @covers \eZ\Publish\Core\Repository\Values\Content\ContentInfo::getProperties
     */
    public function testObjectProperties()
    {
        $object = new ContentInfo;
        $properties = array();
        foreach( $object as $property => $propValue )
        {
            //self::assertNotEquals( 'internalProperty', $property );
            $properties[] = $property;
        }
        self::assertContains( 'contentType', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'id', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'name', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'sectionId', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'currentVersionNo', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'published', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'ownerId', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'modificationDate', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'publishedDate', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'alwaysAvailable', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'remoteId', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'mainLanguageCode', $properties, 'Property not found on ContentInfo' );
        self::assertContains( 'mainLocationId', $properties, 'Property not found on ContentInfo' );
    }
}