<?php
/**
 * File containing the SectionServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the SectionServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionServiceAuthorization
 * @group integration
 */
class SectionServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @expectedException eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testCreateSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::createSection() is not implemented." );
    }

    /**
     * Test for the loadSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::loadSection() is not implemented." );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::updateSection() is not implemented." );
    }

    /**
     * Test for the loadSections() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSectionsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::loadSections() is not implemented." );
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSectionByIdentifierThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::loadSectionByIdentifier() is not implemented." );
    }

    /**
     * Test for the assignSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::assignSection() is not implemented." );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::deleteSection() is not implemented." );
    }
}