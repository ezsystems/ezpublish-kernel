<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\LocationList;
use eZ\Publish\Core\REST\Common;

class LocationListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the LocationList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getLocationListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $locationList = new LocationList( array(), '/content/objects/42/locations' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $locationList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains LocationList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationListElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'LocationList',
            ),
            $result,
            'Invalid <LocationList> element.',
            false
        );
    }

    /**
     * Test if result contains LocationList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'LocationList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.LocationList+xml',
                    'href'       => '/content/objects/42/locations',
                )
            ),
            $result,
            'Invalid <LocationList> attributes.',
            false
        );
    }

    /**
     * Get the LocationList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\LocationList
     */
    protected function getLocationListVisitor()
    {
        return new ValueObjectVisitor\LocationList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
