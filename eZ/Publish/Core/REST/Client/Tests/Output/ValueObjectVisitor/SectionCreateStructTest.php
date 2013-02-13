<?php
/**
 * File containing a SectionCreateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;

class SectionCreateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the SectionCreateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getSectionCreateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionCreateStruct = new Content\SectionCreateStruct(
            array(
                'identifier' => 'some-section',
                'name'       => 'Some Section',
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionCreateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains SectionInput element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionInputElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'SectionInput',
                'children' => array(
                    'less_than'    => 3,
                    'greater_than' => 1,
                )
            ),
            $result,
            'Invalid <SectionInput> element.',
            false
        );
    }

    /**
     * Tests that the result contains SectionInput attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionInputAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'SectionInput',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.SectionInput+xml',
                )
            ),
            $result,
            'Invalid <SectionInput> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains identifier value element
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
            'Invalid or non-existing <SectionInput> identifier value element.',
            false
        );
    }

    /**
     * Tests that the result contains name value element
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
            'Invalid or non-existing <SectionInput> name value element.',
            false
        );
    }

    /**
     * Gets the SectionCreateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\SectionCreateStruct
     */
    protected function getSectionCreateStructVisitor()
    {
        return new ValueObjectVisitor\SectionCreateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
