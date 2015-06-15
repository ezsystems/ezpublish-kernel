<?php
/**
 * File containing the EZP20018LanguageTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacySolr;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Test case for language issues in EZP-20018
 *
 * @issue EZP-20018
 */
class EZP20018LanguageTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();

        $repository = $this->getRepository();

        // Loaded services
        $contentService  = $repository->getContentService();
        $languageService = $repository->getContentLanguageService();

        //Create Por-PT Language
        $langCreateStruct = $languageService->newLanguageCreateStruct();
        $langCreateStruct->languageCode = 'por-PT';
        $langCreateStruct->name = 'Portuguese (portuguese)';
        $langCreateStruct->enabled = true;

        $languageService->createLanguage( $langCreateStruct );

        // Translate "Image" Folder name to por-PT
        $objUpdateStruct = $contentService->newContentUpdateStruct();
        $objUpdateStruct->initialLanguageCode = "eng-US";
        $objUpdateStruct->setField( "name", "Imagens", "por-PT" );

        // @todo Also test always available flag?
        $draft = $contentService->updateContent(
            $contentService->createContentDraft(
                $contentService->loadContentInfo( 49 ) // Images folder
            )->getVersionInfo(),
            $objUpdateStruct
        );

        $contentService->publishVersion(
            $draft->getVersionInfo()
        );
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testSearchOnNotExistingLanguageGivesException()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Skipped on Solr as it is not clear that SPI search should have to validate Criterion values, in this case language code" );
        }

        $query = new Query();
        $query->filter = new LanguageCode( array( "nor-NO" ) );
        $this->getRepository()->getSearchService()->findContent( $query );
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
     */
    public function testSearchOnUsedLanguageGivesOneResult()
    {
        $query = new Query();
        $query->filter = new LanguageCode( array( "por-PT" ), false );
        $results = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 1, $results->totalCount );
        $this->assertCount( 1, $results->searchHits );
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
     */
    public function testSearchOnStandardLanguageGivesManyResult()
    {
        $query = new Query();
        $query->filter = new LanguageCode( array( "eng-US" ), false );
        $query->limit = 50;
        $results = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 16, $results->totalCount );
        $this->assertEquals( $results->totalCount, count( $results->searchHits ) );
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
     */
    public function testSearchOnNotUsedInstalledLanguageGivesNoResult()
    {
        $query = new Query();
        $query->filter = new LanguageCode( array( "eng-GB" ), false );
        $results = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 2, $results->totalCount );
        $this->assertEquals( $results->totalCount, count( $results->searchHits ) );
    }
}
