<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Input\Parser\ContentQuery;

class ContentQueryTest extends BaseTest
{
    /**
     * Tests the LocationCreate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'Criteria' => [
                'SomeCriterion' => '42',
                'SomeOtherCriterion' => 'foo',
            ],
        ];

        $this
            ->getParsingDispatcherMock()
            ->expects($this->exactly(2))
            ->method('parse')
            ->will(
                $this->returnValue(
                    $this->getMock('eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface')
                )
            );

        $result = $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            'eZ\Publish\API\Repository\Values\Content\Query',
            $result
        );

        return $result;
    }

    public function testParseSortClause()
    {
        $inputArray = [
            'Criteria' => [],
            'SortClauses' => [
                ['SortField' => 'NAME', 'SortDirection' => 'ascending'],
                ['SortField' => 'PATH', 'SortDirection' => 'descending'],
                ['SortField' => 'MODIFIED'],
                ['SortField' => 'CREATED'],
                ['SortField' => 'SECTIONIDENTIFER'],
                ['SortField' => 'PRIORITY'],
            ],
        ];

        $result = $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertEquals(new Query\SortClause\ContentName(Query::SORT_ASC), $result->sortClauses[0]);
        $this->assertEquals(new Query\SortClause\Location\Path(Query::SORT_DESC), $result->sortClauses[1]);
        $this->assertEquals(new Query\SortClause\DateModified(Query::SORT_ASC), $result->sortClauses[2]);
        $this->assertEquals(new Query\SortClause\DatePublished(Query::SORT_ASC), $result->sortClauses[3]);
        $this->assertEquals(new Query\SortClause\SectionIdentifier(Query::SORT_ASC), $result->sortClauses[4]);
        $this->assertEquals(new Query\SortClause\Location\Priority(Query::SORT_ASC), $result->sortClauses[5]);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function testParseFieldSortClause()
    {
        $inputArray = [
            'Criteria' => [],
            'SortClauses' => [
                ['SortField' => 'FIELD'],
            ],
        ];

        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\ContentQuery
     */
    protected function internalGetParser()
    {
        return new ContentQuery();
    }
}
