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

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\UserList;
use eZ\Publish\Core\REST\Server\Values\RestUser;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common;

class UserListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getUserListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $userList = new UserList( array(), '/some/path' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains UserList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UserList',
            ),
            $result,
            'Invalid <UserList> element.',
            false
        );
    }

    /**
     * Test if result contains UserList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'UserList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserList+xml',
                    'href'       => '/some/path',
                )
            ),
            $result,
            'Invalid <UserList> attributes.',
            false
        );
    }

    /**
     * Test if UserList visitor visits the children
     */
    public function testUserListVisitsChildren()
    {
        $visitor   = $this->getUserListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $userList = new UserList(
            array(
                new RestUser(
                    new Content(
                        array(
                            'internalFields' => array()
                        )
                    ),
                    $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" ),
                    new ContentInfo(),
                    new Location(),
                    array()
                ),
                new RestUser(
                    new Content(
                        array(
                            'internalFields' => array()
                        )
                    ),
                    $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" ),
                    new ContentInfo(),
                    new Location(),
                    array()
                ),
            ),
            '/some/path'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\RestUser' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userList
        );
    }

    /**
     * Get the UserList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserList
     */
    protected function getUserListVisitor()
    {
        return new ValueObjectVisitor\UserList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
