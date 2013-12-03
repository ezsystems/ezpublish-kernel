<?php
/**
 * File containing the EZP21771EzStringTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for 11+ string issue in EZP-21771
 *
 * @issue EZP-21711
 */
class EZP21771EzStringTest extends BaseTest
{
    /**
     * This is an integration test for issue EZP-21771
     *
     * It shouldn't throw a fatal error when inserting 11 consecutive digits
     * into an eZString field
     */
    public function test11NumbersOnEzString()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        // create content
        $createStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier( 'folder' ),
            'eng-GB'
        );
        $createStruct->setField( 'name', '12345678901' );

        // make a draft
        $draft = $contentService->createContent(
            $createStruct,
            array( $locationService->newLocationCreateStruct( 2 ) )
        );

        // publish
        $contentService->publishVersion( $draft->versionInfo );

        // load the content
        $content = $contentService->loadContent( $draft->versionInfo->contentInfo->id );

        // finaly test if the value is done right
        $this->assertEquals(
            $content->versionInfo->names,
            array( 'eng-GB' => '12345678901' )
        );
    }
}
