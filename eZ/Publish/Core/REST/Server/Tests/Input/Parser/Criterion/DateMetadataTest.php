<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata as DateMetadataCriterion;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion\DateMetadata;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class DateMetadataTest extends BaseTest
{
    public function testParseProvider()
    {
        return [
            [
                ['DateMetadataCriterion' => ['Target' => 'modified', 'Operator' => '=', 'Value' => 1033917596]],
                new DateMetadataCriterion('modified', '=', 1033917596),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'modified', 'Operator' => '<=', 'Value' => 1072180405]],
                new DateMetadataCriterion('modified', '<=', 1072180405),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Operator' => '=', 'Value' => 1033920830]],
                new DateMetadataCriterion('created', '=', 1033920830),
            ],
        ];
    }

    /**
     * Tests the DateMetadata parser.
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
     * Test DateMetadata parser throwing exception on invalid DateMetadataCriterion format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <DateMetadataCriterion> format
     */
    public function testParseExceptionOnInvalidCriterionFormat()
    {
        $inputArray = ['foo'];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetadata parser throwing exception on invalid target format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Target> format
     */
    public function testParseExceptionOnInvalidTargetFormat()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Targett' => 'modified',
                'Operator' => '<=',
                'Value' => 1072180405,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetadata Criterion throwing exception on invalid target value.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown DateMetadata updated
     */
    public function testParseExceptionOnInvalidTargetValue()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'updated',
                'Operator' => '<=',
                'Value' => 1072180405,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DateMetadata parser throwing exception on invalid operator format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Operator> format
     */
    public function testParseExceptionOnInvalidOperatorFormat()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'Operator' => 'LTE',
                'Value' => 1072180405,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test if EQ operator is set if none passed.
     */
    public function testNoOperator()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'Value' => 1072180405,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $parser = $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
        $this->assertEquals('=', $parser->operator);
    }

    /**
     * Test DateMetadata parser throwing exception on invalid value format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid <Value> format
     */
    public function testParseExceptionOnInvalidValueFormat()
    {
        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'created',
                'Operator' => '<=',
                'Value' => 123.456,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the DateMetadata criterion parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\DateMetadata
     */
    protected function internalGetParser()
    {
        return new DateMetadata();
    }
}
