<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;

class EZP22612URLAliasTranslations extends BaseTest
{
    public function setUp()
    {
        $contentService = $this->getRepository()->getContentService();
        $draft = $contentService->createContent(
            $this->getFolderCreateStruct('common'),
            [
                $this->getRepository()->getLocationService()->newLocationCreateStruct(2),
            ]
        );
        $parentContent = $contentService->publishVersion($draft->versionInfo);

        $draft = $contentService->createContent(
            $this->getFolderCreateStruct('alias'),
            [
                $this->getRepository()->getLocationService()->newLocationCreateStruct(
                    $parentContent->contentInfo->mainLocationId
                ),
            ]
        );

        $contentService->publishVersion($draft->versionInfo);
    }

    private function getFolderCreateStruct($name)
    {
        $createStruct = $this->getRepository()->getContentService()->newContentCreateStruct(
            $this->getRepository()->getContentTypeService()->loadContentTypeByIdentifier('folder'),
            'ger-DE'
        );
        $createStruct->setField('name', $name, 'eng-GB');
        $createStruct->setField('name', $name, 'ger-DE');

        return $createStruct;
    }

    /**
     * Test that alias is found (ie. NotFoundException is not thrown).
     */
    public function testURLAliasLoadedInRightLanguage()
    {
        $aliasService = $this->getRepository()->getURLAliasService();
        $alias = $aliasService->lookup('common/alias');

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $alias
        );
    }
}
