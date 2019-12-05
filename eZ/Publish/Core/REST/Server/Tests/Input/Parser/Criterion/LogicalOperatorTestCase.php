<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\REST\Common\Input\Parser as InputParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @internal for internal use by tests
 */
abstract class LogicalOperatorTestCase extends BaseTest
{
    /**
     * Data provider for testParse.
     *
     * @see testParse
     *
     * @return array data sets
     */
    abstract public function getPayloads();

    /**
     * @return string
     */
    abstract protected function getCriterionClass();

    /**
     * @covers \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\LogicalOperator::parse
     *
     * @dataProvider getPayloads
     *
     * @param array $payload
     * @param int $expectedNumberOfCriteria
     */
    public function testParse($payload, $expectedNumberOfCriteria)
    {
        $criterionMock = $this->createMock(Criterion::class);

        $parserMock = $this->createMock(InputParser::class);
        $parserMock->method('parse')->willReturn($criterionMock);

        $result = $this->internalGetParser()->parse(
            $payload,
            $this->buildParsingDispatcher($parserMock)
        );

        self::assertInstanceOf($this->getCriterionClass(), $result);
        self::assertCount($expectedNumberOfCriteria, (array)$result->criteria);
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher
     */
    protected function buildParsingDispatcher(PHPUnit_Framework_MockObject_MockObject $parserMock)
    {
        return new ParsingDispatcher(
            [
                // to test parsing nested combined logical criteria
                'application/vnd.ez.api.internal.criterion.LogicalOr' => new Parser\Criterion\LogicalOr(),
                'application/vnd.ez.api.internal.criterion.LogicalAnd' => new Parser\Criterion\LogicalAnd(),
                'application/vnd.ez.api.internal.criterion.ContentTypeIdentifier' => $parserMock,
                'application/vnd.ez.api.internal.criterion.ContentRemoteId' => new Parser\Criterion\ContentRemoteId(),
                'application/vnd.ez.api.internal.criterion.Field' => $parserMock,
            ]
        );
    }
}
