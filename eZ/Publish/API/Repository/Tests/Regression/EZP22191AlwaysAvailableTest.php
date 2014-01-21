<?php
/**
 * @author: Benjamin Choquet <bchoquet@heliopsis.net>
 * @copyright: Copyright (C) 2014 Heliopsis. All rights reserved.
 * @licence: proprietary
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;

class EZP22191AlwaysAvailableTest extends BaseTest
{
    protected $mainLanguageCode = 'eng-US';
    protected $secondaryLanguageCode = 'eng-GB';
    protected $parentLocationId = 2;

    const UNTRANSLATED_UNAVAILABLE = 'TestEZP22191-untranslated-unavailable';
    const UNTRANSLATED_AVAILABLE = 'TestEZP22191-untranslated-available';
    const TRANSLATED_UNAVAILABLE = 'TestEZP22191-translated-unavailable';
    const TRANSLATED_AVAILABLE = 'TestEZP22191-translated-available';

    public function setUp()
    {
        parent::setUp();

        $contentService = $this->getRepository()->getContentService();
        $locationService = $this->getRepository()->getLocationService();

        //Untranslated - Unavailable
        $draft = $contentService->createContent(
            $this->getNewCreateStruct( self::UNTRANSLATED_UNAVAILABLE, false, false ),
            array( $locationService->newLocationCreateStruct( $this->parentLocationId ) )
        );
        $contentService->publishVersion( $draft->versionInfo );

        //Untranslated - Always available
        $draft = $contentService->createContent(
            $this->getNewCreateStruct( self::UNTRANSLATED_AVAILABLE, false, true ),
            array( $locationService->newLocationCreateStruct( $this->parentLocationId ) )
        );
        $contentService->publishVersion( $draft->versionInfo );


        //Translated - Unavailable
        $draft = $contentService->createContent(
            $this->getNewCreateStruct( self::TRANSLATED_UNAVAILABLE, true, false ),
            array( $locationService->newLocationCreateStruct( $this->parentLocationId ) )
        );
        $contentService->publishVersion( $draft->versionInfo );


        //Translated - Always Available
        $draft = $contentService->createContent(
            $this->getNewCreateStruct( self::TRANSLATED_AVAILABLE, true, true ),
            array( $locationService->newLocationCreateStruct( $this->parentLocationId ) )
        );
        $contentService->publishVersion( $draft->versionInfo );
    }

    /**
     * @param string $remoteId
     * @param bool $translated
     * @param bool $alwaysAvailable
     * @return ContentCreateStruct
     */
    private function getNewCreateStruct( $remoteId, $translated, $alwaysAvailable )
    {
        $contentType = $this->getRepository()->getContentTypeService()->loadContentTypeByIdentifier( 'blog_post' );
        $createStruct = new ContentCreateStruct(
            array(
                'contentType' => $contentType,
                'mainLanguageCode' => $this->mainLanguageCode,
                'remoteId' => $remoteId,
                'alwaysAvailable' => $alwaysAvailable,
            )
        );

        $createStruct->setField( 'title', 'Title in main language', $this->mainLanguageCode );

        if ( $translated )
        {
            $createStruct->setField( 'title', 'Title in secondary language', $this->secondaryLanguageCode );
        }

        return $createStruct;
    }

    public function testLoadContentWithoutLanguageFilter()
    {
        $this->assertLoads( self::UNTRANSLATED_UNAVAILABLE );
        $this->assertLoads( self::UNTRANSLATED_AVAILABLE );
        $this->assertLoads( self::TRANSLATED_UNAVAILABLE );
        $this->assertLoads( self::TRANSLATED_AVAILABLE );
    }

    public function testLoadContentWithMainLanguageFilter()
    {
        $this->assertLoads( self::UNTRANSLATED_UNAVAILABLE, array( $this->mainLanguageCode ) );
        $this->assertLoads( self::UNTRANSLATED_AVAILABLE, array( $this->mainLanguageCode ) );
        $this->assertLoads( self::TRANSLATED_UNAVAILABLE, array( $this->mainLanguageCode ) );
        $this->assertLoads( self::TRANSLATED_AVAILABLE, array( $this->mainLanguageCode ) );
    }

    public function testLoadContentWithSecondaryLanguageFilter()
    {
        $this->assertDoesNotLoad( self::UNTRANSLATED_UNAVAILABLE, array( $this->secondaryLanguageCode ) );
        $this->assertLoads( self::UNTRANSLATED_AVAILABLE, array( $this->secondaryLanguageCode ) );
        $this->assertLoads( self::TRANSLATED_UNAVAILABLE, array( $this->secondaryLanguageCode ) );
        $this->assertLoads( self::TRANSLATED_AVAILABLE, array( $this->secondaryLanguageCode ) );
    }

    public function testLoadContentWithBothLanguagesFilter()
    {
        $this->assertLoads( self::UNTRANSLATED_UNAVAILABLE, array( $this->mainLanguageCode, $this->secondaryLanguageCode ) );
        $this->assertLoads( self::UNTRANSLATED_AVAILABLE, array( $this->mainLanguageCode, $this->secondaryLanguageCode ) );
        $this->assertLoads( self::TRANSLATED_UNAVAILABLE, array( $this->mainLanguageCode, $this->secondaryLanguageCode ) );
        $this->assertLoads( self::TRANSLATED_AVAILABLE, array( $this->mainLanguageCode, $this->secondaryLanguageCode ) );
    }

    /**
     * @param $remoteId
     * @param array $languages
     */
    protected function assertLoads( $remoteId, array $languages = null )
    {
        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content', $this->getRepository()->getContentService()->loadContentByRemoteId( $remoteId, $languages ) );
    }

    /**
     * @param $remoteId
     * @param array $languages
     */
    protected function assertDoesNotLoad( $remoteId, array $languages = null )
    {
        $this->setExpectedException( 'eZ\\Publish\\Core\\Base\\Exceptions\\NotFoundException' );
        $this->getRepository()->getContentService()->loadContentByRemoteId( $remoteId, $languages );
    }
}
