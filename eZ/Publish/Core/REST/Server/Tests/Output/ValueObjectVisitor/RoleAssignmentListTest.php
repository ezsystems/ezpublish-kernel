<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\RestUserRoleAssignment;
use eZ\Publish\Core\REST\Server\Values\RoleAssignmentList;
use eZ\Publish\Core\Repository\Values\User;

class RoleAssignmentListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RoleAssignmentList visitor.
     *
     * @return string
     */
    public function testVisitUserRoleAssignmentList()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignmentList = new RoleAssignmentList([], '42');

        $this->addRouteExpectation(
            'ezpublish_rest_loadRoleAssignmentsForUser',
            ['userId' => 42],
            '/user/users/42/roles'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleAssignmentList element.
     *
     * @param string $result
     *
     * @depends testVisitUserRoleAssignmentList
     */
    public function testResultContainsRoleListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignmentList',
            ],
            $result,
            'Invalid <RoleAssignmentList> element.',
            false
        );
    }

    /**
     * Test if result contains RoleAssignmentList element attributes.
     *
     * @param string $result
     *
     * @depends testVisitUserRoleAssignmentList
     */
    public function testResultContainsUserRoleAssignmentListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignmentList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.RoleAssignmentList+xml',
                    'href' => '/user/users/42/roles',
                ],
            ],
            $result,
            'Invalid <RoleAssignmentList> attributes.',
            false
        );
    }

    /**
     * Test if RoleAssignmentList visitor visits the children.
     */
    public function testRoleAssignmentListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignmentList = new RoleAssignmentList(
            [
                new User\UserRoleAssignment(),
                new User\UserRoleAssignment(),
            ],
            42
        );

        $this->getVisitorMock()->expects($this->exactly(2))
             ->method('visitValueObject')
             ->with($this->isInstanceOf(RestUserRoleAssignment::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );
    }

    /**
     * Test the RoleAssignmentList visitor.
     *
     * @return string
     */
    public function testVisitGroupRoleAssignmentList()
    {
        $this->resetRouterMock();

        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignmentList = new RoleAssignmentList([], '/1/5/777', true);

        $this->addRouteExpectation(
            'ezpublish_rest_loadRoleAssignmentsForUserGroup',
            ['groupPath' => '/1/5/777'],
            '/user/groups/1/5/777/roles'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleAssignmentList element attributes.
     *
     * @param string $result
     *
     * @depends testVisitGroupRoleAssignmentList
     */
    public function testResultContainsGroupRoleAssignmentListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignmentList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.RoleAssignmentList+xml',
                    'href' => '/user/groups/1/5/777/roles',
                ],
            ],
            $result,
            'Invalid <RoleAssignmentList> attributes.',
            false
        );
    }

    /**
     * Get the RoleAssignmentList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RoleAssignmentList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RoleAssignmentList();
    }
}
