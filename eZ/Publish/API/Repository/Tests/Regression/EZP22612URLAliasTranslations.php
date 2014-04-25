<?php
/**
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;

class EZP22612URLAliasTranslations extends BaseTest
{
    public function setUp()
    {
        $contentService = $this->getRepository()->getContentService();
        $draft = $contentService->createContent(
            $this->getFolderCreateStruct( 'common' ),
            array(
                $this->getRepository()->getLocationService()->newLocationCreateStruct( 2 )
            )
        );
        $parentContent = $contentService->publishVersion( $draft->versionInfo );

        $draft = $contentService->createContent(
            $this->getFolderCreateStruct( 'alias' ),
            array(
                $this->getRepository()->getLocationService()->newLocationCreateStruct(
                    $parentContent->contentInfo->mainLocationId
                )
            )
        );

        $contentService->publishVersion( $draft->versionInfo );
    }

    private function getFolderCreateStruct( $name )
    {
        $createStruct = $this->getRepository()->getContentService()->newContentCreateStruct(
            $this->getRepository()->getContentTypeService()->loadContentTypeByIdentifier( 'folder' ),
            'ger-DE'
        );
        $createStruct->setField( 'name', $name, 'eng-GB' );
        $createStruct->setField( 'name', $name, 'ger-DE' );

        return $createStruct;
    }

    public function testURLAliasLoadedInRightLanguage()
    {
        $aliasService = $this->getRepository()->getURLAliasService();
        $alias = $aliasService->lookup( 'common/alias' );
        $this->assertContains( 'eng-GB', $alias->languageCodes );
    }
}
