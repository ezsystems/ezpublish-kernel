<?php
/**
 * File containing the ContentTypeServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\StringLengthValidatorStub;

/**
 * Test case for operations in the ContentTypeServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeServiceAuthorization
 */
class ContentTypeServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::createContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::updateContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentTypeDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::updateContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @depens eZ\Publish\API\Repository\Tests\ContentTypeServiceAuthorizationTest::testAddFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAddFieldDefinitionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::addFieldDefinition() is not implemented." );
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemoveFieldDefinitionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::removeFieldDefinition() is not implemented." );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateFieldDefinitionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::updateFieldDefinition() is not implemented." );
    }

    /**
     * Test for the publishContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishContentTypeDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::publishContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentTypeDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::createContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTypeThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentTypeThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::copyContentType() is not implemented." );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType($contentType, $user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentTypeThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::copyContentType() is not implemented." );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::assignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::unassignContentTypeGroup() is not implemented." );
    }
}
