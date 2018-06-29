<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\Core\REST\Server\Input\Parser\SortClause\Field as FieldParser;
use eZ\Publish\Core\REST\Server\Tests\Input\Parser\BaseTest;

class FieldTest extends BaseTest
{
    /**
     * Tests the Field parser.
     */
    public function testParse()
    {
        $inputArray = [
            'Field' => [
                'identifier' => 'content/field',
                'direction' => Query::SORT_ASC,
            ],
        ];

        $fieldParser = $this->getParser();
        $result = $fieldParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertEquals(
            new Field('content', 'field', Query::SORT_ASC),
            $result,
            'Field parser not created correctly.'
        );
    }

    /**
     * Test Field parser throwing exception on missing sort clause.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage The <Field> sort clause doesn't exist in the input structure
     */
    public function testParseExceptionOnMissingSortClause()
    {
        $inputArray = [
            'name' => 'Keep on mocking in the free world',
        ];

        $fieldParser = $this->getParser();
        $fieldParser->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test Field parser throwing exception on invalid direction format.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Invalid direction format in <Field> sort clause
     */
    public function testParseExceptionOnInvalidDirectionFormat()
    {
        $inputArray = [
            'Field' => [
                'identifier' => 'content/field',
                'direction' => 'mock',
            ],
        ];

        $fieldParser = $this->getParser();
        $fieldParser->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the Field parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\SortClause\Field
     */
    protected function internalGetParser()
    {
        return new FieldParser();
    }
}
