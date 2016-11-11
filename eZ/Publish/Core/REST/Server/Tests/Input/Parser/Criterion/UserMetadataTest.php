<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata as UserMetadataCriterion;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion\UserMetadata;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class UserMetadataTest extends BaseTest
{
    public function testParseProvider()
    {
        return [
            [
                ['UserMetadataCriterion' => ['Target' => 'owner', 'Value' => 14]],
                new UserMetadataCriterion('owner', null, [14]),
            ],
            [
                ['UserMetadataCriterion' => ['Target' => 'owner', 'Value' => '14,15,42']],
                new UserMetadataCriterion('owner', null, [14, 15, 42]),
            ],
            [
                ['UserMetadataCriterion' => ['Target' => 'owner', 'Value' => [14, 15, 42]]],
                new UserMetadataCriterion('owner', null, [14, 15, 42]),
            ],
        ];
    }

    /**
     * Tests the UserMetadata parser.
     *
     * @dataProvider testParseProvider
     */
    public function testParse($data, $expected)
    {
        $userMetadata = $this->getParser();
        $result = $userMetadata->parse($data, $this->getParsingDispatcherMock());

        $this->assertEquals(
            $expected,
            $result,
            'UserMetadata parser not created correctly.'
        );
    }

    /**
     * Test UserMetadata parser throwing exception on invalid UserMetadataCriterion format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <UserMetadataCriterion> format
     */
    public function testParseExceptionOnInvalidCriterionFormat()
    {
        $inputArray = [
            'foo' => 'Michael learns to mock',
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserMetadata parser throwing exception on invalid target format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Target> format
     */
    public function testParseExceptionOnInvalidTargetFormat()
    {
        $inputArray = [
            'UserMetadataCriterion' => [
                'foo' => 'Mock around the clock',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserMetadata parser throwing exception on invalid value format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Value> format
     */
    public function testParseExceptionOnInvalidValueFormat()
    {
        $inputArray = [
            'UserMetadataCriterion' => [
                'Target' => 'Moxette',
                'foo' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserMetadata parser throwing exception on wrong type of value format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Value> format
     */
    public function testParseExceptionOnWrongValueType()
    {
        $inputArray = [
            'UserMetadataCriterion' => [
                'Target' => 'We will mock you',
                'Value' => new \stdClass(),
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the UserMetadata criterion parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\UserMetadata
     */
    protected function internalGetParser()
    {
        return new UserMetadata();
    }
}
