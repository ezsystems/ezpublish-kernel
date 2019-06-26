<?php

/**
 * File containing the LanguageServiceMaximumSupportedLanguagesTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as LegacySetupFactory;

/**
 * Test case for maximum number of languages supported in the LanguageService.
 *
 * @see eZ\Publish\API\Repository\LanguageService
 * @group integration
 * @group language
 */
class LanguageServiceMaximumSupportedLanguagesTest extends BaseTest
{
    /** @var \eZ\Publish\API\Repository\LanguageService */
    private $languageService;

    /** @var array */
    private $createdLanguages = [];

    /**
     * Creates as much languages as possible.
     */
    public function setUp()
    {
        parent::setUp();

        $this->languageService = $this->getRepository()->getContentLanguageService();

        $languageCreate = $this->languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;

        // SKIP If using sqlite, PHP 5.3 and 64bit, tests will fail as int column seems to be limited to 32bit on 64bit
        if (PHP_VERSION_ID < 50400 && PHP_INT_SIZE === 8) {
            $setupFactory = $this->getSetupFactory();
            if ($setupFactory instanceof LegacySetupFactory && $setupFactory->getDB() === 'sqlite') {
                $this->markTestSkipped('Skip on Sqlite, PHP 5.3 and 64bit, as int column is limited to 32bit on 64bit');
            }
        }

        // Create as much languages as possible
        for ($i = count($this->languageService->loadLanguages()) + 1; $i <= 8 * PHP_INT_SIZE - 2; ++$i) {
            $languageCreate->name = "Language $i";
            $languageCreate->languageCode = sprintf('lan-%02d', $i);

            try {
                $this->createdLanguages[] = $this->languageService->createLanguage($languageCreate);
            } catch (\Exception $e) {
                if (PHP_INT_SIZE === 8 && $i === 32) {
                    throw new \Exception('PHP/HHVM is 64bit, but seems INT column in db only supports 32bit', 0, $e);
                }

                throw new \Exception("Unknown issue on iteration $i, PHP_INT_SIZE: " . PHP_INT_SIZE, 0, $e);
            }
        }
    }

    public function tearDown()
    {
        while (($language = array_pop($this->createdLanguages)) !== null) {
            $this->languageService->deleteLanguage($language);
        }

        parent::tearDown();
    }

    /**
     * Test for the number of maximum language that can be created.
     *
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     *
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testNewLanguageCreateStruct
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Maximum number of languages reached!
     */
    public function testCreateMaximumLanguageLimit()
    {
        $languageCreate = $this->languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;

        $languageCreate->name = 'Bad Language';
        $languageCreate->languageCode = 'lan-ER';

        $this->languageService->createLanguage($languageCreate);
    }
}
