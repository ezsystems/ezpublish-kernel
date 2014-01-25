<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\User;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\UserRefList;
use eZ\Publish\Core\REST\Server\Values\RestUser;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use eZ\Publish\Core\REST\Common;

class UserRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserRefList visitor
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor   = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $UserRefList = new UserRefList(
            array(
                new RestUser(
                    new User(),
                    $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" ),
                    new ContentInfo(
                        array(
                            'id' => 14
                        )
                    ),
                    new Location(),
                    array()
                )
            ),
            '/some/path'
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadUser',
            array( 'userId' => $UserRefList->users[0]->contentInfo->id ),
            "/user/users/{$UserRefList->users[0]->contentInfo->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $UserRefList
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
    public function testUserRefListHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserRefList[@href="/some/path"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserRefListMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserRefList[@media-type="application/vnd.ez.api.UserRefList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserRefList/User[@href="/user/users/14"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUserMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/UserRefList/User[@media-type="application/vnd.ez.api.User+xml"]'  );
    }

    /**
     * Get the UserRefList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserRefList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\UserRefList;
    }
}
