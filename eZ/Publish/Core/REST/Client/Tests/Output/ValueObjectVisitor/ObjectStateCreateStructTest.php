<?php

/**
 * File containing a ObjectStateCreateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\API\Repository\Values\ObjectState;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

class ObjectStateCreateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the ObjectStateCreateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $objectStateCreateStruct = new ObjectState\ObjectStateCreateStruct();
        $objectStateCreateStruct->identifier = 'some-state';
        $objectStateCreateStruct->priority = 0;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-GB';
        $objectStateCreateStruct->names = array('eng-GB' => 'Some state EN', 'fre-FR' => 'Some state FR');
        $objectStateCreateStruct->descriptions = array('eng-GB' => 'Description EN', 'fre-FR' => 'Description FR');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectStateCreateStruct
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
            'Invalid or non-existing <ObjectStateCreate> identifier value element.',
            false
        );
    }

    /**
     * Tests that the result contains priority value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'priority',
                'content' => '0',
            ),
            $result,
            'Invalid or non-existing <ObjectStateCreate> priority value element.',
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
            'Invalid or non-existing <ObjectStateCreate> defaultLanguageCode value element.',
            false
        );
    }

    /**
     * Gets the ObjectStateCreateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\ObjectStateCreateStruct
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ObjectStateCreateStruct();
    }
}
