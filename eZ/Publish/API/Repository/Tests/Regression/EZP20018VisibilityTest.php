<?php
/**
 * File containing the EZP20018VisibilityTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Test case for Visibility issues in EZP-20018
 *
 * @issue EZP-20018
 */
class EZP20018VisibilityTest extends BaseTest
{
    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility
     */
    public function testSearchForHiddenContent()
    {
        $query = new Query();
        $query->criterion = new Visibility( Visibility::HIDDEN );
        $results1 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 0, $results1->totalCount );
        $this->assertCount( 0, $results1->searchHits );

         // Hide "Images" Folder
        $locationService  = $this->getRepository()->getLocationService();
        $locationService->hideLocation( $locationService->loadLocation( 54 ) );

        // Assert updated values
        $results2 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 1, $results2->totalCount );
        $this->assertCount( 1, $results2->searchHits );
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility
     */
    public function testSearchForVisibleContent()
    {
        $query = new Query();
        $query->criterion = new Visibility( Visibility::VISIBLE );
        $results1 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 18, $results1->totalCount );
        $this->assertEquals( $results1->totalCount, count( $results1->searchHits ) );

         // Hide "Images" Folder
        $locationService  = $this->getRepository()->getLocationService();
        $locationService->hideLocation( $locationService->loadLocation( 54 ) );

        // Assert updated values
        $results2 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( $results1->totalCount - 1, $results2->totalCount );
        $this->assertEquals( $results2->totalCount, count( $results2->searchHits ) );
    }
}
