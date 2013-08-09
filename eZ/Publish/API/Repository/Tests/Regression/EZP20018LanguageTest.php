<?php
/**
 * File containing the EZP20018LanguageTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
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
        $query = new Query();
        $query->criterion = new LanguageCode( array( "nor-NO" ) );
        $this->getRepository()->getSearchService()->findContent( $query );
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
     */
    public function testSearchOnUsedLanguageGivesOneResult()
    {
        $query = new Query();
        $query->criterion = new LanguageCode( array( "por-PT" ) );
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
        $query->criterion = new LanguageCode( array( "eng-US" ) );
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
        $query->criterion = new LanguageCode( array( "eng-GB" ) );
        $results = $this->getRepository()->getSearchService()->findContent( $query );

        $this->assertEquals( 2, $results->totalCount );
        $this->assertEquals( $results->totalCount, count( $results->searchHits ) );
    }
}
