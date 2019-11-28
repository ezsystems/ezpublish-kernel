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

class LogicalOrTest extends LogicalOperatorTestCase
{
    /**
     * Data provider for LogicalOr::parse test.
     *
     * @see testParse
     *
     * Notice regarding multiple criteria of same type:
     *
     * The XML decoder of eZ is not creating numeric arrays, instead using the tag as the array key.
     * This causes the Field Tag to appear as one-element (type numeric array) and two criteria
     * configuration inside. The logical OR parser will take care of this and return a flat
     * LogicalOr criterion with 4 criteria inside for the following payload:
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
    public function getPayloads()
    {
        return [
            'Simple OR with Field and ContentTypeIdentifierCriterion' => [
                [
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
                ],
                4,
            ],
            'Simple OR with ContentRemoteIdCriterion' => [
                [
                    'OR' => [
                        [
                            'ContentRemoteIdCriterion' => 'remote_id1',
                        ],
                        [
                            'ContentRemoteIdCriterion' => 'remote_id2',
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
        $this->internalGetParser()->parse(['OR' => 'Wrong type'], new ParsingDispatcher());
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\LogicalOr
     */
    protected function internalGetParser()
    {
        return new Parser\Criterion\LogicalOr();
    }

    protected function getCriterionClass()
    {
        return Content\Query\Criterion\LogicalOr::class;
    }
}
