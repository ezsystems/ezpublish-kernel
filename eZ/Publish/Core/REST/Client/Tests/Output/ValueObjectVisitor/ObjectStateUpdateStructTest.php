<?php

/**
 * File containing a ObjectStateUpdateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\API\Repository\Values\ObjectState;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

class ObjectStateUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the ObjectStateUpdateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $objectStateUpdateStruct = new ObjectState\ObjectStateUpdateStruct();
        $objectStateUpdateStruct->identifier = 'some-state';
        $objectStateUpdateStruct->defaultLanguageCode = 'eng-GB';
        $objectStateUpdateStruct->names = array('eng-GB' => 'Some state EN', 'fre-FR' => 'Some state FR');
        $objectStateUpdateStruct->descriptions = array('eng-GB' => 'Description EN', 'fre-FR' => 'Description FR');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectStateUpdateStruct
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the result contains names element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNamesElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'names',
                'children' => array(
                    'count' => 2,
                ),
            ),
            $result,
            'Invalid <names> element.',
            false
        );
    }

    /**
     * Tests that the result contains descriptions element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'descriptions',
                'children' => array(
                    'count' => 2,
                ),
            ),
            $result,
            'Invalid <descriptions> element.',
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
                'content' => 'some-state',
            ),
            $result,
            'Invalid or non-existing <ObjectStateUpdate> identifier value element.',
            false
        );
    }

    /**
     * Tests that the result contains defaultLanguageCode value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDefaultLanguageCodeValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'defaultLanguageCode',
                'content' => 'eng-GB',
            ),
            $result,
            'Invalid or non-existing <ObjectStateUpdate> defaultLanguageCode value element.',
            false
        );
    }

    /**
     * Gets the ObjectStateUpdateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\ObjectStateUpdateStruct
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ObjectStateUpdateStruct();
    }
}
