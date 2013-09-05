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
    public function testVisitSimple()
    {
        $visitor = new CriterionVisitor\FullText();
        $criterion = new Criterion\FullText("Hello");

        $this->assertEquals(
            "text:Hello",
            $visitor->visit( $criterion )
        );
    }
}
