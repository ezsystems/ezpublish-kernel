<?php
/**
 * File containing the ContentAndLocationServiceInteractionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Test case for operations in the ContentService that also require service
 * methods of the LocationService.
 *
 * @see eZ\Publish\API\Repository\ContentService
 * @see eZ\Publish\API\Repository\LocationService
 */
class ContentAndLocationServiceInteractionTest extends BaseTest
{
    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Location id of the "Home" node
        $homeLocationId = 2;

        $contentService     = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService    = $repository->getLocationService();

        // Configure new location
        $locationCreate = $locationService->newLocationCreateStruct( $homeLocationId );

        $locationCreate->priority  = 23;
        $locationCreate->hidden    = true;
        $locationCreate->remoteId  = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );

        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );
        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Create new content object under the specified location
        $content = $contentService->createContent(
            $contentCreate,
            array( $locationCreate )
        );

        /* END: Use Case */

        $location = $locationService->loadLocationByRemoteId(
            '0123456789abcdef0123456789abcdef'
        );

        $this->assertEquals( $content->contentInfo, $location->getContentInfo() );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateContentThrowsIllegalArgumentExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateContentThrowsContentFieldValidationExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateContentThrowsContentValidationExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     *
     */
    public function testCopyContent()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::copyContent() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     *
     */
    public function testCopyContentWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::copyContent() is not implemented." );
    }
}