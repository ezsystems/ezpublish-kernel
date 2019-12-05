<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\Core\REST\Common\Exceptions\Parser as ParserException;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Server\Input\Parser;

class LogicalAndTest extends LogicalOperatorTestCase
{
    /**
     * Data provider for LogicalOr::parse test.
     *
     * @see testParse
     *
     * Notice regarding multiple criteria of same type:
     *
     * The XML decoder of eZ is not creating numeric arrays, instead using the tag as the array key.
     * This causes the ContentTypeIdentifierCriterion Tag to appear as one-element
     * (type numeric array) and two criteria configuration inside. The logical AND parser will take
     * care of this and return a flat LogicalAnd criterion with 3 criteria inside for the following
     * payload:
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
            'Combined AND with nested OR Criterion' => [
                [
                    'AND' => [
                        [
                            'OR' => [
                                'ContentTypeIdentifierCriterion' => [
                                    'article',
                                    'folder',
                                ],
                            ],
                        ],
                        [
                            'OR' => [
                                'ContentTypeIdentifierCriterion' => [
                                    'forum',
                                    'board',
                                ],
                            ],
                        ],
                    ],
                ],
                2,
            ],
            'Combined AND with nested OR Criterion using old format' => [
                [
                    'AND' => [
                        [
                            'OR' => [
                                [
                                    'ContentTypeIdentifierCriterion' => [
                                        'article',
                                        'folder',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'OR' => [
                                'ContentTypeIdentifierCriterion' => [
                                    'forum',
                                    'board',
                                ],
                            ],
                        ],
                    ],
                ],
                2,
            ],
        ];
    }

    public function testThrowsExceptionOnInvalidAndStatement()
    {
        $this->expectException(ParserException::class);
        $this->internalGetParser()->parse(['AND' => 'Should be an array'], new ParsingDispatcher());
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\LogicalAnd
     */
    protected function internalGetParser()
    {
        return new Parser\Criterion\LogicalAnd();
    }

    protected function getCriterionClass()
    {
        return Content\Query\Criterion\LogicalAnd::class;
    }
}
