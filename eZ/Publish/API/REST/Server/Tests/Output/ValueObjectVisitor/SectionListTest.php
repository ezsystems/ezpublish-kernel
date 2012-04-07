<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\API\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\Values\SectionList;
use eZ\Publish\API\Repository\Values\Content;

class SectionListTest extends ValueObjectVisitorBaseTest
{
    /**
     * testVisit
     *
     * @return void
     */
    public function testVisit()
    {
        $visitor   = $this->getSectionListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionList = new SectionList( array() );

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
     * testResultContainsSectionListElement
     *
     * @param string $result
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
     * testResultContainsSectionListAttributes
     *
     * @param string $result
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
     * testSectionListVisitsChildren
     *
     * @param mixed $result
     * @return void
     */
    public function testSectionListVisitsChildren()
    {
        $visitor   = $this->getSectionListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionList = new SectionList( array(
            new Content\Section(),
            new Content\Section(),
        ) );

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
     * @return \eZ\Publish\API\REST\Server\Output\ValueObjectVisitor\SectionList
     */
    protected function getSectionListVisitor()
    {
        return new ValueObjectVisitor\SectionList();
    }
}
