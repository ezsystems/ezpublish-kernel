<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\REST\Common;

class UserGroupRoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the UserGroupRoleAssignment visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getUserGroupRoleAssignmentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $userGroupRoleAssignment = new User\UserGroupRoleAssignment(
            array(
                'role' => new User\Role(
                    array(
                        'id'         => 42,
                        'identifier' => 'some-role'
                    )
                ),
                'userGroup' => new User\UserGroup(
                    array(
                        'content' => new Content(
                            array(
                                'versionInfo' => new VersionInfo(
                                    array(
                                        'contentInfo' => new ContentInfo(
                                            array(
                                                'id' => 14
                                            )
                                        )
                                    )
                                ),
                                'internalFields' => array()
                            )
                        )
                    )
                )
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupRoleAssignment
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains RoleAssignment element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleAssignment',
                'children' => array(
                    'count' => 1
                )
            ),
            $result,
            'Invalid <RoleAssignment> element.',
            false
        );
    }

    /**
     * Test if result contains RoleAssignment element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleAssignment',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleAssignment+xml',
                    'href'       => '/user/groups/14/roles/42',
                )
            ),
            $result,
            'Invalid <RoleAssignment> attributes.',
            false
        );
    }

    /**
     * Test if result contains Role element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsRoleElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Role'
            ),
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Role',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Role+xml',
                    'href'       => '/user/roles/42',
                )
            ),
            $result,
            'Invalid <Role> attributes.',
            false
        );
    }

    /**
     * Test if UserGroupRoleAssignment visitor visits the children
     */
    public function testRoleAssignmentListVisitsChildren()
    {
        $visitor   = $this->getUserGroupRoleAssignmentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $limitation = new User\Limitation\RoleLimitation( 'Class' );
        $limitation->limitationValues = array( 1, 2, 3 );

        $userGroupRoleAssignment = new User\UserGroupRoleAssignment(
            array(
                'limitation' => $limitation,
                'role' => new User\Role(
                    array(
                        'id'         => 42,
                        'identifier' => 'some-role'
                    )
                ),
                'userGroup' => new User\UserGroup(
                    array(
                        'content' => new Content(
                            array(
                                'versionInfo' => new VersionInfo(
                                    array(
                                        'contentInfo' => new ContentInfo(
                                            array(
                                                'id' => 14
                                            )
                                        )
                                    )
                                ),
                                'internalFields' => array()
                            )
                        )
                    )
                )
            )
        );

        $this->getVisitorMock()->expects( $this->exactly( 1 ) )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\User\\Limitation' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupRoleAssignment
        );
    }

    /**
     * Get the UserGroupRoleAssignment visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UserGroupRoleAssignment
     */
    protected function getUserGroupRoleAssignmentVisitor()
    {
        return new ValueObjectVisitor\UserGroupRoleAssignment(
            new Common\UrlHandler\eZPublish()
        );
    }
}
