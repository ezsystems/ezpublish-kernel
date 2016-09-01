<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Values\ResourceRouteReference;
use eZ\Publish\Core\REST\Server\Values\RestUserGroup;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestUserGroupTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitWithoutEmbeddedVersion()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restUserGroup = $this->getBasicRestUserGroup();

        $userGroupPath = implode('/', $restUserGroup->mainLocation->path);

        $this->setVisitValueObjectExpectations([
            new ResourceRouteReference('ezpublish_rest_loadUserGroup', ['groupPath' => $userGroupPath]),
            new ResourceRouteReference('ezpublish_rest_loadContentType', ['contentTypeId' => $restUserGroup->contentInfo->contentTypeId]),
            new ResourceRouteReference('ezpublish_rest_loadContentVersions', ['contentId' => $restUserGroup->contentInfo->id]),
            new ResourceRouteReference('ezpublish_rest_loadSection', ['sectionId' => $restUserGroup->contentInfo->sectionId]),
            new ResourceRouteReference('ezpublish_rest_loadLocation', ['locationPath' => $userGroupPath]),
            new ResourceRouteReference('ezpublish_rest_loadLocationsForContent', ['contentId' => $restUserGroup->contentInfo->id]),
            new ResourceRouteReference('ezpublish_rest_loadUser', ['userId' => $restUserGroup->contentInfo->ownerId]),
            $this->isInstanceOf('eZ\Publish\Core\REST\Server\Values\Version'),
            new ResourceRouteReference('ezpublish_rest_loadUserGroup', ['groupPath' => '1/2']),
            new ResourceRouteReference('ezpublish_rest_loadSubUserGroups', ['groupPath' => $userGroupPath]),
            new ResourceRouteReference('ezpublish_rest_loadUsersFromGroup', ['groupPath' => $userGroupPath]),
            new ResourceRouteReference('ezpublish_rest_loadRoleAssignmentsForUserGroup', ['groupPath' => $userGroupPath]),
        ]);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restUserGroup
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestUserGroup()
    {
        return new RestUserGroup(
            new Values\User\UserGroup(),
            $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType'),
            new ContentInfo(
                array(
                    'id' => 'content23',
                    'name' => 'Sindelfingen',
                    'sectionId' => 'section23',
                    'currentVersionNo' => 5,
                    'published' => true,
                    'ownerId' => 'user23',
                    'modificationDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'publishedDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'alwaysAvailable' => true,
                    'remoteId' => 'abc123',
                    'mainLanguageCode' => 'eng-US',
                    'mainLocationId' => 'location23',
                    'contentTypeId' => 'contentType23',
                )
            ),
            new Values\Content\Location(
                array(
                    'pathString' => '/1/2/23',
                    'path' => array(1, 2, 23),
                )
            ),
            array()
        );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@id="content23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupMediaTypeWithoutVersionCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@media-type="application/vnd.ez.api.UserGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupRemoteIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@remoteId="abc123"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupTypeMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/ContentType[@media-type="application/vnd.ez.api.ContentType+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/name[text()="Sindelfingen"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Versions[@media-type="application/vnd.ez.api.VersionList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Section[@media-type="application/vnd.ez.api.Section+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/MainLocation[@media-type="application/vnd.ez.api.Location+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Locations[@media-type="application/vnd.ez.api.LocationList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Owner[@media-type="application/vnd.ez.api.User+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/mainLanguageCode[text()="eng-US"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/alwaysAvailable[text()="true"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testParentUserGroupMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/ParentUserGroup[@media-type="application/vnd.ez.api.UserGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSubgroupsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Subgroups[@media-type="application/vnd.ez.api.UserGroupList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUsersMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Users[@media-type="application/vnd.ez.api.UserList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Roles[@media-type="application/vnd.ez.api.RoleAssignmentList+xml"]');
    }

    /**
     * Get the UserGroup visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestUserGroup
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestUserGroup();
    }
}
