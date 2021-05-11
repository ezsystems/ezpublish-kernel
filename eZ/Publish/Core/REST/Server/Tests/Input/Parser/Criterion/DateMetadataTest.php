<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata as DateMetadataCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion\DateMetadata;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class DateMetadataTest extends BaseTest
{
    public function testParseProvider()
    {
        return [
            [
                ['DateMetadataCriterion' => ['Target' => 'modified', 'Value' => [14, 1620739489], 'Operator' => 'BETWEEN']],
                new DateMetadataCriterion('modified', Operator::BETWEEN, [14, 1620739489]),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'modified', 'Value' => 14, 'Operator' => 'GT']],
                new DateMetadataCriterion('modified', Operator::GT, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 14, 'Operator' => 'GTE']],
                new DateMetadataCriterion('created', Operator::GTE, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 14, 'Operator' => 'EQ']],
                new DateMetadataCriterion('created', Operator::EQ, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 1620739489, 'Operator' => 'LT']],
                new DateMetadataCriterion('created', Operator::LT, 1620739489),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 1620739489, 'Operator' => 'LTE']],
                new DateMetadataCriterion('created', Operator::LTE, 1620739489),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => [14, 58, 167, 165245, 1620739489], 'Operator' => 'IN']],
                new DateMetadataCriterion('created', Operator::IN, [14, 58, 167, 165245, 1620739489]),
            ],
        ];
    }

    /**
     * Tests the DateMetaData parser.
     *
     * @dataProvider testParseProvider
     */
    public function testParse($data, $expected)
    {
        $dateMetadata = $this->getParser();
        $result = $dateMetadata->parse($data, $this->getParsingDispatcherMock());

        $this->assertEquals(
            $expected,
            $result,
            'DateMetadata parser not created correctly.'
        );
    }

    /**
     * Test DateMetaData parser throwing exception on invalid UserMetadataCriterion format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <DateMetaDataCriterion> format
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
     * Test DateMetaData parser throwing exception on invalid target format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Target> format
     */
    public function testParseExceptionOnInvalidTargetFormat()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'foo' => 'Mock around the clock',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetaData parser throwing exception on wrong target format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Target> format
     */
    public function testParseExceptionOnWrongTargetType()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'Mock around the clock',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetaData parser throwing exception on invalid value format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Value> format
     */
    public function testParseExceptionOnInvalidValueFormat()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'foo' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetaData parser throwing exception on wrong type of value format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Value> format
     */
    public function testParseExceptionOnWrongValueType()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'Value' => new \stdClass(),
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetaData parser throwing exception on invalid value format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Operator> format
     */
    public function testParseExceptionOnInvalidOperatorFormat()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the DateMetaData criterion parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\DateMetadata
     */
    protected function internalGetParser()
    {
        return new DateMetadata();
    }
}
