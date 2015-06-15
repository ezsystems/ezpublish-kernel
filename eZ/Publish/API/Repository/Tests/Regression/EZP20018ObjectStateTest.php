<?php
/**
 * File containing the EZP20018ObjectStateTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ObjectStateId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Test case for ObjectState issues in EZP-20018
 *
 * @issue EZP-20018
 */
class EZP20018ObjectStateTest extends BaseTest
{
    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ObjectStateId
     */
    public function testSearchForNonUsedObjectState()
    {
        $query = new Query();
        $query->filter = new ObjectStateId( 2 );
        $results1 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 0, $results1->totalCount );
        $this->assertCount( 0, $results1->searchHits );

        // Assign and make sure it updates
        $stateService = $this->getRepository()->getObjectStateService();

        $stateService->setContentState(
            $this->getRepository()->getContentService()->loadContentInfo( 52 ),
            $stateService->loadObjectStateGroup( 2 ),
            $stateService->loadObjectState( 2 )
        );

        $results2 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 1, $results2->totalCount );
        $this->assertCount( $results2->totalCount, $results2->searchHits );

    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ObjectStateId
     */
    public function testSearchForUsedObjectState()
    {
        $query = new Query();
        $query->filter = new ObjectStateId( 1 );
        $query->limit = 50;
        $results1 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 18, $results1->totalCount );
        $this->assertEquals( $results1->totalCount, count( $results1->searchHits ) );

        // Assign and make sure it updates
        $stateService = $this->getRepository()->getObjectStateService();

        $stateService->setContentState(
            $this->getRepository()->getContentService()->loadContentInfo( 52 ),
            $stateService->loadObjectStateGroup( 2 ),
            $stateService->loadObjectState( 2 )
        );

        $results2 = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 17, $results2->totalCount );
        $this->assertCount( $results2->totalCount, $results2->searchHits );
    }
}
