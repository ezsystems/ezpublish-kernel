<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseContentTypeServiceTest;

class ContentTypeDuplicateIdentifierTest extends BaseContentTypeServiceTest
{
    public function testDuplicateIdentifierCreate()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeDraft1 = $this->createContentTypeDraft();
        $contentTypeDraft2 = $this->createContentTypeDraft();

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft1 );
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft2 );

        $publishedType1 = $contentTypeService->loadContentType( $contentTypeDraft1->id );
        $publishedType2 = $contentTypeService->loadContentType( $contentTypeDraft2->id );

        $this->assertNotEquals(
            $publishedType1->identifier,
            $publishedType2->identifier
        );
    }

    public function testDuplicateIdentifierNotFound()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeDraft1 = $this->createContentTypeDraft();
        $contentTypeDraft2 = $this->createContentTypeDraft();

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft1 );
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft2 );

        $contentTypeService->loadContentTypeByIdentifier( $contentTypeDraft1->identifier );
    }
}
