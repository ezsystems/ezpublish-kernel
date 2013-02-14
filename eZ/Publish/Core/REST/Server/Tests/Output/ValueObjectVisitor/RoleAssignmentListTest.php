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
use eZ\Publish\Core\REST\Server\Values\RoleAssignmentList;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class RoleAssignmentListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RoleAssignmentList visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getRoleAssignmentListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $roleAssignmentList = new RoleAssignmentList( array(), '42' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains RoleAssignmentList element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleAssignmentList',
            ),
            $result,
            'Invalid <RoleAssignmentList> element.',
            false
        );
    }

    /**
     * Test if result contains RoleAssignmentList element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleAssignmentList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleAssignmentList+xml',
                    'href'       => '/user/users/42/roles',
                )
            ),
            $result,
            'Invalid <RoleAssignmentList> attributes.',
            false
        );
    }

    /**
     * Test if RoleAssignmentList visitor visits the children
     */
    public function testRoleAssignmentListVisitsChildren()
    {
        $visitor   = $this->getRoleAssignmentListVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $roleAssignmentList = new RoleAssignmentList(
            array(
                new User\UserRoleAssignment(),
                new User\UserRoleAssignment()
            ),
            42
        );

        $this->getVisitorMock()->expects( $this->exactly( 2 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\RestUserRoleAssignment' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );
    }

    /**
     * Get the RoleAssignmentList visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RoleAssignmentList
     */
    protected function getRoleAssignmentListVisitor()
    {
        return new ValueObjectVisitor\RoleAssignmentList(
            new Common\UrlHandler\eZPublish()
        );
    }
}
