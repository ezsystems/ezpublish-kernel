<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Solr\Tests\Content\Search\CriterionVisitor\FullTextTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Tests\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Tests\TestCase;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

/**
 * Test case for FullText criterion visitor
 *
 * @covers \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\FullText
 */
class FullTextTest extends TestCase
{
    protected function getFullTextCriterionVisitor()
    {
        $fieldMap = $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Solr\\Content\\Search\\FieldMap',
            array( 'getFieldTypes' ),
            array(),
            '',
            false
        );

        $fieldMap
            ->expects( $this->any() )
            ->method( 'getFieldTypes' )
            ->will( $this->returnValue( array( 'title' => array( 'title_1_s', 'title_2_s' ) ) ) );

        return new CriterionVisitor\FullText( $fieldMap );
    }

    public function testVisitSimple()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText( "Hello" );

        $this->assertEquals(
            "(text:Hello)",
            $visitor->visit( $criterion )
        );
    }

    public function testVisitFuzzy()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText( "Hello" );
        $criterion->fuzzyness = .5;

        $this->assertEquals(
            "(text:Hello~0.5)",
            $visitor->visit( $criterion )
        );
    }

    public function testVisitBoost()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText( "Hello" );
        $criterion->boost = array( 'title' => 2 );

        $this->assertEquals(
            "(text:Hello) OR (title_1_s:Hello^2) OR (title_2_s:Hello^2)",
            $visitor->visit( $criterion )
        );
    }

    public function testVisitBoostUnknownField()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText( "Hello" );
        $criterion->boost = array(
            'unknown_field' => 2,
        );

        $this->assertEquals(
            "(text:Hello)",
            $visitor->visit( $criterion )
        );
    }

    public function testVisitFuzzyBoost()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText( "Hello" );
        $criterion->fuzzyness = .5;
        $criterion->boost = array( 'title' => 2 );

        $this->assertEquals(
            "(text:Hello~0.5) OR (title_1_s:Hello^2~0.5) OR (title_2_s:Hello^2~0.5)",
            $visitor->visit( $criterion )
        );
    }
}
