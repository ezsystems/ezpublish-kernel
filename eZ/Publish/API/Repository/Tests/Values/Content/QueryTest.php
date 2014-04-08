<?php
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * File containing the QueryTest class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

class QueryTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $criterion = $this->createCriterionMock( 'criterion = value AND otherCriterion in (value1, value2)' );

        $sortClause = $this->createSortClauseMock( 'sortClause ASCENDING, otherSortClauseDescending' );

        $query = new Query();
        $query->filter = $criterion;
        $query->sortClauses = array( $sortClause );

        self::assertEquals(
            'criterion = value AND otherCriterion in (value1, value2) SORT BY sortClause ASCENDING, otherSortClauseDescending',
            (string)$query
        );
    }

    /**
     * Returns a SortClause object that expects __fromString() to be called once and return $toStringExpectation.
     * @param string $toStringExpectation The string the method is expected to return on __fromString.
     * @return Query\Criterion|PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCriterionMock( $toStringExpectation )
    {
        return $this->createMock( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion', $toStringExpectation );
    }

    /**
     * Returns a SortClause object that expects __fromString() to be called once and return $toStringExpectation.
     * @param string $toStringExpectation The string the method is expected to return on __fromString.
     * @return Query\SortClause|PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSortClauseMock( $toStringExpectation )
    {
        return $this->createMock( '\eZ\Publish\API\Repository\Values\Content\Query\SortClause', $toStringExpectation );
    }

    /**
     * Returns a mock of $class that expects __fromString() to be called once and return $toStringExpectation.
     * @param string $toStringExpectation The string the method is expected to return on __fromString.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock( $class, $toStringExpectation )
    {
        $sortClause = $this
            ->getMockBuilder( $class, '__toString' )
            ->disableOriginalConstructor()
            ->getMock();

        $sortClause->expects( $this->once() )
            ->method( '__toString' )
            ->will( $this->returnValue( $toStringExpectation ) );

        return $sortClause;
    }
}
