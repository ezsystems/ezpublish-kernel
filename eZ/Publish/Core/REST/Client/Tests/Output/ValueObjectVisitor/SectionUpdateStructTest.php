<?php

/**
 * File containing a SectionUpdateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\Content;

class SectionUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the SectionUpdateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $sectionUpdateStruct = new Content\SectionUpdateStruct(
            array(
                'identifier' => 'some-section',
                'name' => 'Some Section',
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionUpdateStruct
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the result contains SectionInput element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionInputElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'SectionInput',
                'children' => array(
                    'less_than' => 3,
                    'greater_than' => 1,
                ),
            ),
            $result,
            'Invalid <SectionInput> element.',
            false
        );
    }

    /**
     * Tests that the result contains SectionInput attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionInputAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'SectionInput',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.SectionInput+xml',
                ),
            ),
            $result,
            'Invalid <SectionInput> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains identifier value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'identifier',
                'content' => 'some-section',
            ),
            $result,
            'Invalid or non-existing <SectionInput> identifier value element.',
            false
        );
    }

    /**
     * Tests that the result contains name value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNameValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'name',
                'content' => 'Some Section',
            ),
            $result,
            'Invalid or non-existing <SectionInput> name value element.',
            false
        );
    }

    /**
     * Gets the SectionUpdateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\SectionUpdateStruct
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\SectionUpdateStruct();
    }
}
