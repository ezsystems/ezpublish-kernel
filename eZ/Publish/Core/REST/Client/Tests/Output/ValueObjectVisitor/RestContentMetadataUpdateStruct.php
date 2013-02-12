<?php
/**
 * File containing a SectionIncludingContentMetadataUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct;
use eZ\Publish\Core\REST\Common;

class RestContentMetadataUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the SectionIncludingContentMetadataUpdateStructTest visitor
     *
     * @return string
     */
    public function testVisitComplete()
    {
        $visitor   = $this->getSectionIncludingContentMetadataUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionCreatestruct = new RestContentMetadataUpdateStruct(
            $this->getValidValues()
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionCreatestruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that result contains ContentUpdate element
     *
     * @param string $result
     *
     * @depends testVisitComplete
     */
    public function testResultContainsContentUpdateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ContentUpdate',
                'children' => array(
                    'less_than'    => 3,
                    'greater_than' => 1,
                )
            ),
            $result,
            'Invalid <ContentUpdate> element.',
            false
        );
    }

    /**
     * Tests that result contains Section element
     *
     * @param string $result
     *
     * @depends testVisitComplete
     */
    public function testResultSectionElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Section',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Section+xml',
                    'href'       => '/content/sections/23'
                )
            ),
            $result,
            'Invalid attributes for <Section> element.',
            false
        );
    }

    /**
     * Tests Owner element attributes
     *
     * @param string $result
     *
     * @depends testVisitComplete
     */
    public function testResultOwnerElement( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Owner',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.User+xml',
                    'href'       => '/user/users/42'
                )
            ),
            $result,
            'Invalid attributes for <Owner> element.',
            false
        );
    }

    /**
     * Tests attributes for Section element
     */
    public function testVisitNoSectionUpdate()
    {
        $visitor   = $this->getSectionIncludingContentMetadataUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $values = $this->getValidValues();
        unset( $values['sectionId'] );

        $sectionCreatestruct = new RestContentMetadataUpdateStruct(
            $values
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionCreatestruct
        );

        $result = $generator->endDocument( null );

        $this->assertTag(
            array(
                'tag' => 'Section',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Section+xml',
                )
            ),
            $result,
            'Invalid attributes for <Section> element.',
            false
        );
    }

    /**
     * Tests the Owner element attributes
     */
    public function testVisitNoOwnerUpdate()
    {
        $visitor   = $this->getSectionIncludingContentMetadataUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $values = $this->getValidValues();
        unset( $values['ownerId'] );

        $sectionCreatestruct = new RestContentMetadataUpdateStruct(
            $values
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionCreatestruct
        );

        $result = $generator->endDocument( null );

        $this->assertTag(
            array(
                'tag' => 'Owner',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.User+xml',
                )
            ),
            $result,
            'Invalid attributes for <Owner> element.',
            false
        );
    }

    /**
     * Returns a set of valid struct values.
     *
     * @return array
     */
    protected function getValidValues()
    {
        return array(
            'sectionId' => '/content/sections/23',
            'ownerId'   => '/user/users/42',
            // @todo: Add missing properties
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\SectionIncludingContentMetadataUpdateStruct
     */
    protected function getSectionIncludingContentMetadataUpdateStructVisitor()
    {
        return new ValueObjectVisitor\SectionIncludingContentMetadataUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
