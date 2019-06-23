<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\RoleList;
use eZ\Publish\Core\Repository\Values\User;

class RoleListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RoleList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleList = new RoleList([], '/user/roles');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleList',
            ],
            $result,
            'Invalid <RoleList> element.',
            false
        );
    }

    /**
     * Test if result contains RoleList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.RoleList+xml',
                    'href' => '/user/roles',
                ],
            ],
            $result,
            'Invalid <RoleList> attributes.',
            false
        );
    }

    /**
     * Test if RoleList visitor visits the children.
     */
    public function testRoleListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleList = new RoleList(
            [
                new User\Role(),
                new User\Role(),
            ],
            '/user/roles'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(Role::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleList
        );
    }

    /**
     * Get the RoleList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RoleList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RoleList();
    }
}
