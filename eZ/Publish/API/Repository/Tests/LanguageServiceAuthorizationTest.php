<?php
/**
 * File containing the LanguageServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the LanguageServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LanguageServiceAuthorization
 * @group integration
 */
class LanguageServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LanguageService::createLanguage() is not implemented." );
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testUpdateLanguageName
     */
    public function testUpdateLanguageNameThrowsUnauthorizedException()
    {

        $this->markTestIncomplete( "@TODO: Test for LanguageService::updateLanguageName() is not implemented." );
    }

    /**
     * Test for the enableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::enableLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testEnableLanguage
     */
    public function testEnableLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LanguageService::enableLanguage() is not implemented." );
    }

    /**
     * Test for the disableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::disableLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testDisableLanguage
     */
    public function testDisableLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LanguageService::disableLanguage() is not implemented." );
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testDeleteLanguage
     */
    public function testDeleteLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LanguageService::deleteLanguage() is not implemented." );
    }
}