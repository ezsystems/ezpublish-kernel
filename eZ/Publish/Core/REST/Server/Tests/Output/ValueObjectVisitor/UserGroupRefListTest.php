<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\UserGroupRefList;
use eZ\Publish\Core\REST\Server\Values\RestUserGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;

class UserGroupRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserGroupRefList visitor.
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $UserGroupRefList = new UserGroupRefList(
            [
                new RestUserGroup(
                    new UserGroup(),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(
                        [
                            'pathString' => '/1/5/14',
                            'path' => [1, 5, 14],
                        ]
                    ),
                    []
                ),
                new RestUserGroup(
                    new UserGroup(),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(
                        [
                            'pathString' => '/1/5/13',
                            'path' => [1, 5, 13],
                        ]
                    ),
                    []
                ),
            ],
            '/some/path',
            14
        );

        $groupPath = trim($UserGroupRefList->userGroups[0]->mainLocation->pathString, '/');
        $this->addRouteExpectation(
            'ezpublish_rest_loadUserGroup',
            ['groupPath' => $groupPath],
            "/user/groups/{$groupPath}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_unassignUserFromUserGroup',
            ['userId' => $UserGroupRefList->userId, 'groupPath' => 14],
            '/user/users/14/groups/14'
        );

        $groupPath = trim($UserGroupRefList->userGroups[1]->mainLocation->pathString, '/');
        $this->addRouteExpectation(
            'ezpublish_rest_loadUserGroup',
            ['groupPath' => '1/5/13'],
            "/user/groups/{$groupPath}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_unassignUserFromUserGroup',
            ['userId' => $UserGroupRefList->userId, 'groupPath' => 13],
            '/user/users/14/groups/13'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $UserGroupRefList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserGroupRefListHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList[@href="/some/path"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserGroupRefListMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList[@media-type="application/vnd.ez.api.UserGroupRefList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1][@href="/user/groups/1/5/14"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1][@media-type="application/vnd.ez.api.UserGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupUnassignHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1]/unassign[@href="/user/users/14/groups/14"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupUnassignMethodCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1]/unassign[@method="DELETE"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2][@href="/user/groups/1/5/13"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2][@media-type="application/vnd.ez.api.UserGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupUnassignHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2]/unassign[@href="/user/users/14/groups/13"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupUnassignMethodCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2]/unassign[@method="DELETE"]');
    }

    /**
     * Get the UserGroupRefList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserGroupRefList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\UserGroupRefList();
    }
}
