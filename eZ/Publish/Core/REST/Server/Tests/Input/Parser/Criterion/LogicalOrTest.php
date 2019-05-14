<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class LogicalOrTest extends BaseTest
{
    /**
     * Test parsing of OR statement.
     *
     * Notice regarding multiple criteria of same type:
     *
     * The XML decoder of EZ is not creating numeric arrays, instead using the tag as the array key. See
     * variable $logicalOrParsedFromXml. This causes the Field Tag to appear as one-element
     * (type numeric array) and two criteria configuration inside. The logical or parser will take care
     * of this and return a flatt LogicalOr criterion with 4 criteria inside.
     *
     * ```
     * <OR>
     *   <ContentTypeIdentifierCriterion>author</ContentTypeIdentifierCriterion>
     *   <ContentTypeIdentifierCriterion>book</ContentTypeIdentifierCriterion>
     *   <Field>
     *     <name>title</name>
     *     <operator>EQ</operator>
     *     <value>Contributing to projects</value>
     *   </Field>
     *   <Field>
     *     <name>title</name>
     *     <operator>EQ</operator>
     *     <value>Contributing to projects</value>
     *   </Field>
     * </OR>
     * ```
     */
    public function testParseLogicalOr()
    {
        $logicalOrParsedFromXml = [
            'OR' => [
                'ContentTypeIdentifierCriterion' => [
                    0 => 'author',
                    1 => 'book',
                ],
                'Field' => [
                    0 => [
                        'name' => 'title',
                        'operator' => 'EQ',
                        'value' => 'Contributing to projects',
                    ],
                    1 => [
                        'name' => 'title',
                        'operator' => 'EQ',
                        'value' => 'Contributing to projects',
                    ],
                ],
            ],
        ];

        $criterionMock = $this->createMock(Content\Query\Criterion::class, [], [], '', false);

        $parserMock = $this->createMock(\eZ\Publish\Core\REST\Common\Input\Parser::class);
        $parserMock->method('parse')->willReturn($criterionMock);

        $result = $this->internalGetParser()->parse($logicalOrParsedFromXml, new ParsingDispatcher([
            'application/vnd.ez.api.internal.criterion.ContentTypeIdentifier' => $parserMock,
            'application/vnd.ez.api.internal.criterion.Field' => $parserMock,
        ]));

        self::assertInstanceOf(Content\Query\Criterion\LogicalOr::class, $result);
        self::assertCount(4, (array)$result->criteria);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testThrowsExceptionOnInvalidAndStatement()
    {
        $this->internalGetParser()->parse(['OR' => 'Wrong type'], new ParsingDispatcher());
    }

    /**
     * @return Parser\Criterion\LogicalOr
     */
    protected function internalGetParser()
    {
        return new Parser\Criterion\LogicalOr();
    }
}
