<?php
/**
 * File containing the QueryTest class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Query;
use PHPUnit_Framework_TestCase;

class QueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerForTestToString
     */
    public function testToString( $criterionString, $sortClauseStringArray, $expectedQueryString )
    {
        $query = new Query();
        if ( $criterionString !== false )
        {
            $query->filter = $this->createCriterionMock( $criterionString );
        }

        if ( $sortClauseStringArray !== false )
        {
            foreach ( $sortClauseStringArray as $sortClauseString )
            {
                $query->sortClauses[] = $this->createSortClauseMock( $sortClauseString );
            }
        }

        self::assertEquals( $expectedQueryString, (string)$query );
    }

    public function providerForTestToString()
    {
        return array(
            // both sortClause and filter
            array(
                'criterion = value AND otherCriterion in (value1, value2)',
                array( 'sortClause1 ascending', 'sortClause2 descending' ),
                'criterion = value AND otherCriterion in (value1, value2) SORT BY sortClause1 ascending, sortClause2 descending'
            ),
            // no filter
            array(
                false,
                array( 'sortClause1 ascending', 'sortClause2 descending' ),
                'SORT BY sortClause1 ascending, sortClause2 descending'
            ),
            // no sortClause
            array(
                'criterion = value AND otherCriterion in (value1, value2)',
                false,
                'criterion = value AND otherCriterion in (value1, value2)'
            ),
            // nothing
            array(
                false,
                false,
                ''
            )
        );
    }

    /**
     * Returns a SortClause object that expects __fromString() to be called once and return $toStringExpectation.
     * @param string $toStringExpectation The string the method is expected to return on __fromString.
     * @return Query\Criterion|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCriterionMock( $toStringExpectation )
    {
        return $this->createMock( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion', $toStringExpectation );
    }

    /**
     * Returns a SortClause object that expects __fromString() to be called once and return $toStringExpectation.
     * @param string $toStringExpectation The string the method is expected to return on __fromString.
     * @return Query\SortClause|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSortClauseMock( $toStringExpectation )
    {
        return $this->createMock( '\eZ\Publish\API\Repository\Values\Content\Query\SortClause', $toStringExpectation );
    }

    /**
     * Returns a mock of $class that expects __fromString() to be called once and return $toStringExpectation.
     * @param string $toStringExpectation The string the method is expected to return on __fromString.
     * @return \PHPUnit_Framework_MockObject_MockObject
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
