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
use eZ\Publish\Core\REST\Server\Values\SectionList;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;

class SectionListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the SectionList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getSectionListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionList = new SectionList( array(), '/content/sections' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains SectionList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'SectionList',
            ),
            $result,
            'Invalid <SectionList> element.',
            false
        );
    }

    /**
     * Test if result contains SectionList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'SectionList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.SectionList+xml',
                    'href'       => '/content/sections',
                )
            ),
            $result,
            'Invalid <SectionList> attributes.',
            false
        );
    }

    /**
     * Test if SectionList visitor visits the children
     */
    public function testSectionListVisitsChildren()
    {
        $visitor   = $this->getSectionListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionList = new SectionList(
            array(
                new Content\Section(),
                new Content\Section(),
            ),
            '/content/sections'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Section' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionList
        );
    }

    /**
     * Get the SectionList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\SectionList
     */
    protected function getSectionListVisitor()
    {
        return new ValueObjectVisitor\SectionList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
