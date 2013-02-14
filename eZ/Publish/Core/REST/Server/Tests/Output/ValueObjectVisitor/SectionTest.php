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
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;

class SectionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Section visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getSectionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $section = new Content\Section(
            array(
                'id'         => 23,
                'identifier' => 'some-section',
                'name'       => 'Some Section',
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $section
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Section element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Section',
                'children' => array(
                    'less_than'    => 4,
                    'greater_than' => 2,
                )
            ),
            $result,
            'Invalid <Section> element.',
            false
        );
    }

    /**
     * Test if result contains Section element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Section',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Section+xml',
                    'href'       => '/content/sections/23',
                )
            ),
            $result,
            'Invalid <Section> attributes.',
            false
        );
    }

    /**
     * Test if result contains sectionId value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'sectionId',
                'content'  => '23',
            ),
            $result,
            'Invalid or non-existing <Section> sectionId value element.',
            false
        );
    }

    /**
     * Test if result contains identifier value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'identifier',
                'content'  => 'some-section',
            ),
            $result,
            'Invalid or non-existing <Section> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains name value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNameValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'name',
                'content'  => 'Some Section',
            ),
            $result,
            'Invalid or non-existing <Section> name value element.',
            false
        );
    }

    /**
     * Get the Section visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Section
     */
    protected function getSectionVisitor()
    {
        return new ValueObjectVisitor\Section(
            new Common\UrlHandler\eZPublish()
        );
    }
}
