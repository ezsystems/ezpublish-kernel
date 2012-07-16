<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Tests\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\API\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\REST\Common;

class SectionUpdateTest extends ValueObjectVisitorBaseTest
{
    /**
     * testVisit
     *
     * @return void
     */
    public function testVisit()
    {
        $visitor   = $this->getSectionUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionUpdatestruct = new Content\SectionUpdateStruct( array(
            'identifier' => 'some-section',
            'name'       => 'Some Section',
        ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionUpdatestruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * testResultContainsSectionElement
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsSectionElement( $result )
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
     * testResultContainsSectionAttributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsSectionAttributes( $result )
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
     * testResultContainsIdentifierValueElement
     *
     * @param string $result
     * @return void
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
            'Invalid <SectionInput> attributes.',
            false
        );
    }

    /**
     * testResultContainsNameValueElement
     *
     * @param string $result
     * @return void
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
            'Invalid <SectionInput> attributes.',
            false
        );
    }

    /**
     * @return \eZ\Publish\API\REST\Client\Output\ValueObjectVisitor\Section
     */
    protected function getSectionUpdateStructVisitor()
    {
        return new ValueObjectVisitor\SectionUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
