<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Tests\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Solr\Tests\TestCase;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;

/**
 * Test case for FullText criterion visitor.
 *
 * @covers \eZ\Publish\Core\Search\Solr\Content\CriterionVisitor\FullText
 */
class FullTextTest extends TestCase
{
    protected function getFullTextCriterionVisitor(array $fieldNames = array())
    {
        $fieldNameResolver = $this->getMock(
            '\\eZ\\Publish\\Core\\Search\\Common\\FieldNameResolver',
            array('getFieldNames'),
            array(),
            '',
            false
        );

        $fieldNameResolver
            ->expects($this->any())
            ->method('getFieldNames')
            ->with(
                $this->isInstanceOf('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion'),
                $this->isType('string')
            )
            ->will(
                $this->returnValue($fieldNames)
            );

        return new CriterionVisitor\FullText($fieldNameResolver);
    }

    public function testVisitSimple()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');

        $this->assertEquals(
            '(text:Hello)',
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzy()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;

        $this->assertEquals(
            '(text:Hello~0.5)',
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoost()
    {
        $visitor = $this->getFullTextCriterionVisitor(array('title_1_s', 'title_2_s'));

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            '(text:Hello) OR (title_1_s:Hello^2) OR (title_2_s:Hello^2)',
            $visitor->visit($criterion)
        );
    }

    public function testVisitBoostUnknownField()
    {
        $visitor = $this->getFullTextCriterionVisitor();

        $criterion = new Criterion\FullText('Hello');
        $criterion->boost = array(
            'unknown_field' => 2,
        );

        $this->assertEquals(
            '(text:Hello)',
            $visitor->visit($criterion)
        );
    }

    public function testVisitFuzzyBoost()
    {
        $visitor = $this->getFullTextCriterionVisitor(array('title_1_s', 'title_2_s'));

        $criterion = new Criterion\FullText('Hello');
        $criterion->fuzziness = .5;
        $criterion->boost = array('title' => 2);

        $this->assertEquals(
            '(text:Hello~0.5) OR (title_1_s:Hello^2~0.5) OR (title_2_s:Hello^2~0.5)',
            $visitor->visit($criterion)
        );
    }
}
