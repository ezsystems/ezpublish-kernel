<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\Core\REST\Common\Values;

class RestObjectStateTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestObjectState visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $objectState = new Values\RestObjectState(
            new ObjectState(
                [
                    'id' => 42,
                    'identifier' => 'test-state',
                    'priority' => '0',
                    'mainLanguageCode' => 'eng-GB',
                    'languageCodes' => ['eng-GB', 'eng-US'],
                    'names' => [
                        'eng-GB' => 'State name EN',
                        'eng-US' => 'State name EN US',
                    ],
                    'descriptions' => [
                        'eng-GB' => 'State description EN',
                        'eng-US' => 'State description EN US',
                    ],
                ]
            ),
            21
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadObjectState',
            ['objectStateGroupId' => $objectState->groupId, 'objectStateId' => $objectState->objectState->id],
            "/content/objectstategroups/{$objectState->groupId}/objectstates/{$objectState->objectState->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadObjectStateGroup',
            ['objectStateGroupId' => $objectState->groupId],
            "/content/objectstategroups/{$objectState->groupId}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectState
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ObjectState element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectState',
                'children' => [
                    'count' => 8,
                ],
            ],
            $result,
            'Invalid <ObjectState> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectState element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectState',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ObjectState+xml',
                    'href' => '/content/objectstategroups/21/objectstates/42',
                ],
            ],
            $result,
            'Invalid <ObjectState> attributes.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateGroup element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
            ],
            $result,
            'Invalid <ObjectStateGroup> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateGroup element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ObjectStateGroup+xml',
                    'href' => '/content/objectstategroups/21',
                ],
            ],
            $result,
            'Invalid <ObjectStateGroup> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <ObjectState> id value element.',
            false
        );
    }

    /**
     * Test if result contains identifier value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'test-state',
            ],
            $result,
            'Invalid or non-existing <ObjectState> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains priority value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'priority',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <ObjectState> priority value element.',
            false
        );
    }

    /**
     * Test if result contains defaultLanguageCode value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDefaultLanguageCodeValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'defaultLanguageCode',
                'content' => 'eng-GB',
            ],
            $result,
            'Invalid or non-existing <ObjectState> defaultLanguageCode value element.',
            false
        );
    }

    /**
     * Test if result contains languageCodes value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLanguageCodesValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'languageCodes',
                'content' => 'eng-GB,eng-US',
            ],
            $result,
            'Invalid or non-existing <ObjectState> languageCodes value element.',
            false
        );
    }

    /**
     * Test if result contains names element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNamesElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'names',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <names> element.',
            false
        );
    }

    /**
     * Test if result contains descriptions element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'descriptions',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <descriptions> element.',
            false
        );
    }

    /**
     * Get the ObjectState visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestObjectState
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestObjectState();
    }
}
