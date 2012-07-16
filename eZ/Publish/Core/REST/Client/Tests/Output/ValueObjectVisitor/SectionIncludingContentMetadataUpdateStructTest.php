<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Values\SectionIncludingContentMetadataUpdateStruct;
use eZ\Publish\Core\REST\Common;

class SectionIncludingContentMetadataUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return void
     */
    public function testVisitComplete()
    {
        $visitor   = $this->getSectionIncludingContentMetadataUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $sectionCreatestruct = new SectionIncludingContentMetadataUpdateStruct(
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
     * @param string $result
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
     * @param string $result
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
     * @param string $result
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
     * @return void
     */
    public function testVisitNoSectionUpdate()
    {
        $visitor   = $this->getSectionIncludingContentMetadataUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $values = $this->getValidValues();
        unset( $values['sectionId'] );

        $sectionCreatestruct = new SectionIncludingContentMetadataUpdateStruct(
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
     * @return void
     */
    public function testVisitNoOwnerUpdate()
    {
        $visitor   = $this->getSectionIncludingContentMetadataUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $values = $this->getValidValues();
        unset( $values['ownerId'] );

        $sectionCreatestruct = new SectionIncludingContentMetadataUpdateStruct(
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
            // TODO: Add missing properties
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\Section
     */
    protected function getSectionIncludingContentMetadataUpdateStructVisitor()
    {
        return new ValueObjectVisitor\SectionIncludingContentMetadataUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
