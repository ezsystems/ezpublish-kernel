<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\Repository\Values\User\UserGroup;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\UserGroupRefList;
use eZ\Publish\Core\REST\Server\Values\RestUserGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common;

class UserGroupRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserGroupRefList visitor
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor   = $this->getUserGroupRefListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $UserGroupRefList = new UserGroupRefList(
            array(
                new RestUserGroup(
                    new UserGroup(),
                    $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" ),
                    new ContentInfo(),
                    new Location(
                        array(
                            'pathString' => '/1/5/14',
                            'path' => array( 1, 5, 14 )
                        )
                    ),
                    array()
                ),
                new RestUserGroup(
                    new UserGroup(),
                    $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" ),
                    new ContentInfo(),
                    new Location(
                        array(
                            'pathString' => '/1/5/13',
                            'path' => array( 1, 5, 13 )
                        )
                    ),
                    array()
                )
            ),
            '/some/path',
            14
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $UserGroupRefList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserGroupRefListHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList[@href="/some/path"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserGroupRefListMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList[@media-type="application/vnd.ez.api.UserGroupRefList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[1][@href="/user/groups/1/5/14"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[1][@media-type="application/vnd.ez.api.UserGroup+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupUnassignHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[1]/unassign[@href="/user/users/14/groups/14"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstUserGroupUnassignMethodCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[1]/unassign[@method="DELETE"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[2][@href="/user/groups/1/5/13"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[2][@media-type="application/vnd.ez.api.UserGroup+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupUnassignHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[2]/unassign[@href="/user/users/14/groups/13"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondUserGroupUnassignMethodCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserGroupRefList/UserGroup[2]/unassign[@method="DELETE"]'  );
    }

    /**
     * Get the UserGroupRefList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserGroupRefList
     */
    protected function getUserGroupRefListVisitor()
    {
        return new ValueObjectVisitor\UserGroupRefList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
