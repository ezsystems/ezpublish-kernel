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
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\UserList;
use eZ\Publish\Core\REST\Server\Values\RestUser;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;

class UserListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userList = new UserList([], '/some/path');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains UserList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UserList',
            ],
            $result,
            'Invalid <UserList> element.',
            false
        );
    }

    /**
     * Test if result contains UserList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UserList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.UserList+xml',
                    'href' => '/some/path',
                ],
            ],
            $result,
            'Invalid <UserList> attributes.',
            false
        );
    }

    /**
     * Test if UserList visitor visits the children.
     */
    public function testUserListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userList = new UserList(
            [
                new RestUser(
                    new Content(
                        [
                            'internalFields' => [],
                        ]
                    ),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(),
                    []
                ),
                new RestUser(
                    new Content(
                        [
                            'internalFields' => [],
                        ]
                    ),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(),
                    []
                ),
            ],
            '/some/path'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(RestUser::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userList
        );
    }

    /**
     * Get the UserList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\UserList();
    }
}
