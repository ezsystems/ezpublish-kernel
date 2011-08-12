<?php
/**
 * File containing the QueryBuilderTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\CriterionFactory,
    ezp\Content\Query\Builder;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\Query\Builder
     */
    private $qb;

    public function setUp()
    {
        $this->qb = new Builder();
    }

    /**
     * Global test for criterion factory getters
     * @dataProvider providerForTestCriterionGetter
     */
    public function testCriterionGetter( $accessor, $operator, $parameters )
    {
        $factory = $this->qb->$accessor;
        self::assertInstanceOf( 'ezp\\Content\\CriterionFactory', $factory );
        // $factory->operator( $param1, $param2 )
        $criterion = call_user_func_array( array( $factory, $operator ), $parameters );
        self::assertInstanceOf( 'ezp\\Persistence\\Content\\Criterion', $criterion );
    }

    public static function providerForTestCriterionGetter()
    {
        return array(
            array( 'contentId', 'in', array( array( 1, 2 )  ) ),
            array( 'contentId', 'eq', array( 1 ) ),

            array( 'contentTypeId', 'in', array( array( 1, 2 ) ) ),
            array( 'contentTypeId', 'eq', array( 'article' ) ),

            array( 'contentTypeGroupId', 'in', array( array( 1, 2 ) ) ),
            array( 'contentTypeGroupId', 'eq', array( 'content' ) ),

            array( 'field', 'eq', array( 'testfield', 'my test' ) ),
            array( 'field', 'like', array( 'testfield', 'my test*' ) ),
            array( 'field', 'in', array( 'testfield', array( 'a', 'b', 'c' ) ) ),
            array( 'field', 'gt', array( 'testfield', 1 ) ),
            array( 'field', 'gte', array( 'testfield', 1 ) ),
            array( 'field', 'lt', array( 'testfield', 1 ) ),
            array( 'field', 'lte', array( 'testfield', 1 ) ),
            array( 'field', 'between', array( 'testfield', 5, 10 ) ),

            array( 'fullText', 'like', array( 'testvalue%' ) ),

            array( 'locationId', 'eq', array( 1 ) ),
            array( 'locationId', 'in', array( array( 1, 2, 3 ) ) ),

            array( 'parentLocationId', 'eq', array( 1 ) ),
            array( 'parentLocationId', 'in', array( array( 1, 2, 3 ) ) ),

            array( 'dateMetadata', 'eq', array( 'modified', time() ) ),
            array( 'dateMetadata', 'eq', array( 'created', time() ) ),
            array( 'dateMetadata', 'gt', array( 'modified', time() ) ),
            array( 'dateMetadata', 'gt', array( 'created', time() ) ),
            array( 'dateMetadata', 'gte', array( 'modified', time() ) ),
            array( 'dateMetadata', 'gte', array( 'created', time() ) ),
            array( 'dateMetadata', 'lt', array( 'modified', time() ) ),
            array( 'dateMetadata', 'lt', array( 'created', time() ) ),
            array( 'dateMetadata', 'lte', array( 'modified', time() ) ),
            array( 'dateMetadata', 'lte', array( 'created', time() ) ),
            array( 'dateMetadata', 'between', array( 'modified', strtotime( 'last month' ), strtotime( 'last week' ) ) ),
            array( 'dateMetadata', 'between', array( 'created', strtotime( 'last month' ), strtotime( 'last week' ) ) ),
            array( 'dateMetadata', 'in', array( 'modified', array( strtotime( 'today' ), strtotime( 'yesterday' ) ) ) ),
            array( 'dateMetadata', 'in', array( 'created', array( strtotime( 'today' ), strtotime( 'yesterday' ) ) ) ),

            array( 'remoteId', 'in', array( array( 1, 2 ) ) ),
            array( 'remoteId', 'eq', array( 1 ) ),

            array( 'sectionId', 'in', array( array( 1, 2 ) ) ),
            array( 'sectionId', 'eq', array( 1 ) ),

            array( 'subtreeId', 'in', array( array( 1, 2 ) ) ),
            array( 'subtreeId', 'eq', array( 1 ) ),

            array( 'urlAlias', 'in', array( array( '/articles/*', '/blog/*' ) ) ),
            array( 'urlAlias', 'eq', array( '/homepage' ) ),
            array( 'urlAlias', 'like', array( '/blog/*' ) ),
        );
    }

    /**
     * @dataProvider providerForTestLogical
     */
    public function testLogical( $method, $criteriaCount, $expectedClass )
    {
        $criteria = array();
        for ( $i = 0; $i < $criteriaCount; $i++ )
        {
            $criteria[] = $this->qb->field->eq( 'title', md5( time() ) );
        }

        $criterion = call_user_func_array( array( $this->qb, $method ), $criteria );

        self::assertInstanceOf( $expectedClass, $criterion );
        self::assertEquals( $criteriaCount, count( $criterion->criteria ) );
    }

    public static function providerForTestLogical()
    {
        return array(
            array( 'or', 2, 'ezp\\Persistence\\Content\\Criterion\\LogicalOr', 2 ),
            array( 'and', 2, 'ezp\\Persistence\\Content\\Criterion\\LogicalAnd', 2 ),
            array( 'not', 1, 'ezp\\Persistence\\Content\\Criterion\\LogicalNot', 1 ),
        );
    }
}
?>
