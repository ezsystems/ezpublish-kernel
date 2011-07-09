<?php
/**
 * File containing the ezp\Content\QueryBuilder class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content_tests
 */

namespace ezp\Content\Tests;
use ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\CriterionFactory;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\QueryBuilder
     */
    private $qb;

    public function setUp()
    {
        $this->qb = new \ezp\Content\QueryBuilder();
    }

    /**
     * Global test for criterion factory getters
     * @dataProvider providerForTestCriterionGetter
     */
    public function testCriterionGetter( $accessor )
    {
        self::assertInstanceOf( 'ezp\Content\CriterionFactory', $this->qb->$accessor );
    }

    public static function providerForTestCriterionGetter()
    {
        return array(
            array( 'contentId' ),
            array( 'contentType' ),
            array( 'contentTypeGroup' ),
            array( 'field' ),
            array( 'fullText' ),
            array( 'LocationId' ),
            array( 'metaData' ),
            array( 'permission' ),
            array( 'remoteId' ),
            array( 'section' ),
            array( 'subTree' ),
            array( 'urlAlias' )
        );
    }

    /*public function testMetaData()
    {
        $cf = $this->qb->metaData;
        self::assertInstanceOf( 'ezp\Content\CriterionFactory', $cf );
    }

    public function testField()
    {
        $ret = $this->qb->field->eq( 'title', 'My shiny title' );
        self::assertEquals( $ret, $this->qb );
    }*/
}
?>