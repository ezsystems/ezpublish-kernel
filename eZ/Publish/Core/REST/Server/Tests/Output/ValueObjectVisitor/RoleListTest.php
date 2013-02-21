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
use eZ\Publish\Core\REST\Server\Values\RoleList;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class RoleListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RoleList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getRoleListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $roleList = new RoleList( array(), '/user/roles' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains RoleList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleList',
            ),
            $result,
            'Invalid <RoleList> element.',
            false
        );
    }

    /**
     * Test if result contains RoleList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleList+xml',
                    'href'       => '/user/roles',
                )
            ),
            $result,
            'Invalid <RoleList> attributes.',
            false
        );
    }

    /**
     * Test if RoleList visitor visits the children
     */
    public function testRoleListVisitsChildren()
    {
        $visitor   = $this->getRoleListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $roleList = new RoleList(
            array(
                new User\Role(),
                new User\Role(),
            ),
            '/user/roles'
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\User\\Role' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleList
        );
    }

    /**
     * Get the RoleList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RoleList
     */
    protected function getRoleListVisitor()
    {
        return new ValueObjectVisitor\RoleList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
