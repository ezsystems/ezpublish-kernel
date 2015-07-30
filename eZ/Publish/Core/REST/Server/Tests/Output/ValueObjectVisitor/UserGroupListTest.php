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
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\UserGroupList;
use eZ\Publish\Core\REST\Server\Values\RestUserGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;

class UserGroupListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserGroupList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userGroupList = new UserGroupList(array(), '/some/path');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains UserGroupList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserGroupListElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'UserGroupList',
            ),
            $result,
            'Invalid <UserGroupList> element.',
            false
        );
    }

    /**
     * Test if result contains UserGroupList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserGroupListAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'UserGroupList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserGroupList+xml',
                    'href' => '/some/path',
                ),
            ),
            $result,
            'Invalid <UserGroupList> attributes.',
            false
        );
    }

    /**
     * Test if UserGroupList visitor visits the children.
     */
    public function testUserGroupListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userGroupList = new UserGroupList(
            array(
                new RestUserGroup(
                    new Content(
                        array(
                            'internalFields' => array(),
                        )
                    ),
                    $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType'),
                    new ContentInfo(),
                    new Location(),
                    array()
                ),
                new RestUserGroup(
                    new Content(
                        array(
                            'internalFields' => array(),
                        )
                    ),
                    $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType'),
                    new ContentInfo(),
                    new Location(),
                    array()
                ),
            ),
            '/some/path'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf('eZ\\Publish\\Core\\REST\\Server\\Values\\RestUserGroup'));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupList
        );
    }

    /**
     * Get the UserGroupList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserGroupList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\UserGroupList();
    }
}
