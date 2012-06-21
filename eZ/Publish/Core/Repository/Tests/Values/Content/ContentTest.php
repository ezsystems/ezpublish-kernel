<?php
/**
 * File containing the ContentTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Values\Content;
use eZ\Publish\Core\Repository\Values\Content\Content,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

/**
 *
 */
class ContentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\Content\Content::getIterator
     * @covers \eZ\Publish\Core\Repository\Values\Content\Content::getProperties
     */
    public function testObjectProperties()
    {
        $object = new Content( array( 'internalFields' => array() ) );
        $properties = array();
        foreach( $object as $property => $propValue )
        {
            self::assertNotEquals( 'internalFields', $property );
            $properties[] = $property;
        }
        self::assertContains( 'id', $properties, 'Property not found on Content' );
        self::assertContains( 'fields', $properties, 'Property not found on Content' );
        self::assertContains( 'relations', $properties, 'Property not found on Content' );
        self::assertContains( 'versionInfo', $properties, 'Property not found on Content' );
        self::assertContains( 'contentInfo', $properties, 'Property not found on Content' );
        self::assertContains( 'contentType', $properties, 'Property not found on Content' );
    }
}