<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\REST\Common\Input\Parser as InputParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class LogicalAndTest extends BaseTest
{
    /**
     * Logical parsing of AND statement.
     *
     * @dataProvider getPayloads
     *
     * Notice regarding multiple criteria of same type:
     *
     * The XML decoder of EZ is not creating numeric arrays, instead using the tag as the array key. See
     * variable $logicalAndParsedFromXml. This causes the ContentTypeIdentifierCriterion-Tag to appear as one-element
     * (type numeric array) and two criteria configuration inside. The logical or parser will take care
     * of this and return a flatt LogicalAnd criterion with 3 criteria inside.
     *
     * ```
     * <AND>
     *   <ContentTypeIdentifierCriterion>author</ContentTypeIdentifierCriterion>
     *   <ContentTypeIdentifierCriterion>book</ContentTypeIdentifierCriterion>
     *   <Field>
     *     <name>title</name>
     *     <operator>EQ</operator>
     *     <value>Contributing to projects</value>
     *   </Field>
     * </AND>
     * ```
     * @param $expectedNumberOfCriteria
     * @param array $payload
     */
    public function testParseLogicalAnd($payload, $expectedNumberOfCriteria)
    {
        $criterionMock = $this->createMock(Criterion::class);

        $parserMock = $this->createMock(InputParser::class);
        $parserMock->method('parse')->willReturn($criterionMock);

        $result = $this->internalGetParser()->parse(
            $payload,
            new ParsingDispatcher(
                [
                    'application/vnd.ez.api.internal.criterion.ContentTypeIdentifier' => $parserMock,
                    'application/vnd.ez.api.internal.criterion.Field' => $parserMock,
                ]
            )
        );

        self::assertInstanceOf(Content\Query\Criterion\LogicalAnd::class, $result);
        self::assertCount($expectedNumberOfCriteria, (array)$result->criteria);
    }

    /**
     * Data provider for testParseLogicalAnd.
     *
     * @see testParseLogicalAnd
     *
     * @return array
     */
    public function getPayloads()
    {
        return [
            'Simple AND Criterion' => [
                [
                    'AND' => [
                        'ContentTypeIdentifierCriterion' => [
                            0 => 'author',
                            1 => 'book',
                        ],
                        'Field' => [
                            'name' => 'title',
                            'operator' => 'EQ',
                            'value' => 'Contributing to projects',
                        ],
                    ],
                ],
                3,
            ],
        ];
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testThrowsExceptionOnInvalidAndStatement()
    {
        $this->internalGetParser()->parse(['AND' => 'Should be an array'], new ParsingDispatcher());
    }

    /**
     * @return Parser\Criterion\LogicalAnd
     */
    protected function internalGetParser()
    {
        return new Parser\Criterion\LogicalAnd();
    }
}
