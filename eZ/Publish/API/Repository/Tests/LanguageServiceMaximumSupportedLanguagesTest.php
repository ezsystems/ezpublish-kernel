<?php
/**
 * File containing the LanguageServiceMaximumSupportedLanguagesTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for maximum number of languages supported in the LanguageService.
 *
 * @see eZ\Publish\API\Repository\LanguageService
 * @group integration
 * @group language
 */
class LanguageServiceMaximumSupportedLanguagesTest extends BaseTest
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    private $languageService;

    /**
     * @var array
     */
    private $createdLanguages = array();

    /**
     * Creates as much languages as possible
     */
    public function setUp()
    {
        parent::setUp();

        $this->languageService = $this->getRepository()->getContentLanguageService();

        $languageCreate = $this->languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;

        // Create as much languages as possible
        for ( $i = count( $this->languageService->loadLanguages() ) + 1; $i <= 8 * PHP_INT_SIZE - 2; ++$i )
        {
            $languageCreate->name = "Language $i";
            $languageCreate->languageCode = sprintf( "lan-%02d", $i );

            $this->createdLanguages[] = $this->languageService->createLanguage( $languageCreate );
        }
    }

    public function tearDown()
    {
        while ( ( $language = array_pop( $this->createdLanguages ) ) !== null )
        {
            $this->languageService->deleteLanguage( $language );
        }

        parent::tearDown();
    }

    /**
     * Test for the number of maximum language that can be created.
     *
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     *
     * @depends \eZ\Publish\API\Repository\Tests\LanguageServiceTest::testNewLanguageCreateStruct
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Maximum number of languages reached!
     */
    public function testCreateMaximumLanguageLimit()
    {
        $languageCreate = $this->languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;

        $languageCreate->name = "Bad Language";
        $languageCreate->languageCode = "lan-ER";

        $this->languageService->createLanguage( $languageCreate );
    }
}
