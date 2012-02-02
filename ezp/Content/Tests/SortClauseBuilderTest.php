<?php
/**
 * File containing the ezp\Content\Tests\SortClauseBuilder class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;

use ezp\Content\Query\SortClauseBuilder,
    ezp\Content\Query;

class SortClauseBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\Query\SortClauseBuilder
     */
    private $sb;

    public function setUp()
    {
        $this->sb = new SortClauseBuilder();
    }

    /**
     * @dataProvider providerForTestCall
     */
    public function testCall( $sortClauseCall, $sortClauseClass, $sortClauseField, $extraParameters = array() )
    {
        array_push( $extraParameters, Query::SORT_ASC );
        $sortClause = call_user_func_array( array( $this->sb, $sortClauseCall ), $extraParameters );
        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Query\\SortClause', $sortClause );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\Query\\SortClause\\$sortClauseClass", $sortClause );
        self::assertEquals( $sortClauseField, $sortClause->target );
        self::assertEquals( Query::SORT_ASC, $sortClause->direction );

    }

    public static function providerForTestCall()
    {
        return array(
            // array( <sortClauseCall>, <SortClauseClass>, <SortClauseField> ),
            array( 'sectionIdentifier', 'SectionIdentifier', 'section_identifier' ),
            array( 'sectionName', 'SectionName', 'section_name' ),
            array( 'contentName', 'ContentName', 'content_name' ),
            array( 'dateCreated', 'DateCreated', 'date_created' ),
            array( 'dateModified', 'DateModified', 'date_modified' ),
            array( 'locationPriority', 'LocationPriority', 'location_priority' ),
            array( 'locationPath', 'LocationPath', 'location_path' ),
            array( 'locationPathString', 'LocationPathString', 'location_path_string' ),
            array( 'locationDepth', 'LocationDepth', 'location_depth' ),
            array( 'field', 'Field', 'field', array( 'article', 'title' ) ),
        );
    }

    /**
     * @expectedException \BadFunctionCallException
     */
    function testCallInvalidSortClause()
    {
        $this->sb->funkySortClause();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testCallInvalidSortDirection()
    {
        $this->sb->sectionIdentifier( 'foobar' );
    }
}
?>
