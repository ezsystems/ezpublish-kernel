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
use eZ\Publish\Core\REST\Common\Values\Root;
use eZ\Publish\Core\REST\Common;

class RootTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Role visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $role = new Root;

        $this->addRouteExpectation( 'ezpublish_rest_redirectContent', array(), '/content/objects' );
        $this->addRouteExpectation( 'ezpublish_rest_listContentTypes', array(), '/content/types' );
        $this->addRouteExpectation( 'ezpublish_rest_loadUsers', array(), '/user/users' );
        $this->addRouteExpectation( 'ezpublish_rest_listRoles', array(), '/user/roles' );
        $this->addRouteExpectation(
            'ezpublish_rest_loadLocation',
            array( 'locationPath' => '1/2' ),
            '/content/locations/1/2'
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadUserGroup',
            array( 'groupPath' => '1/5' ),
            '/user/groups/1/5'
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadLocation',
            array( 'locationPath' => '1/43' ),
            '/content/locations/1/43'
        );
        $this->addRouteExpectation( 'ezpublish_rest_loadTrashItems', array(), '/content/trash' );
        $this->addRouteExpectation( 'ezpublish_rest_listSections', array(), '/content/sections' );
        $this->addRouteExpectation( 'ezpublish_rest_createView', array(), '/content/views' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $role
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Root',
                'children' => array(
                    'count' => 10
                )
            ),
            $result,
            'Invalid <Root> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRootAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Root',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Root+xml',
                )
            ),
            $result,
            'Invalid <Root> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'content'
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'content',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/objects'
                )
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypesTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'contentTypes'
            ),
            $result,
            'Invalid <contentTypes> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypesTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'contentTypes',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeInfoList+xml',
                    'href' => '/content/types'
                )
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUsersTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'users'
            ),
            $result,
            'Invalid <users> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUsersTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'users',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/user/users'
                )
            ),
            $result,
            'Invalid <users> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRolesTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'roles'
            ),
            $result,
            'Invalid <contentTypes> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRolesTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'roles',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleList+xml',
                    'href' => '/user/roles'
                )
            ),
            $result,
            'Invalid <roles> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootLocationTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'rootLocation'
            ),
            $result,
            'Invalid <rootLocation> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootLocationTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'rootLocation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1/2'
                )
            ),
            $result,
            'Invalid <rootLocation> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootUserGroupTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'rootUserGroup'
            ),
            $result,
            'Invalid <rootUserGroup> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootUserGroupTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'rootUserGroup',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserGroup+xml',
                    'href' => '/user/groups/1/5'
                )
            ),
            $result,
            'Invalid <rootUserGroup> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootMediaFolderTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'rootMediaFolder'
            ),
            $result,
            'Invalid <rootMediaFolder> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootMediaFolderTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'rootMediaFolder',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1/43'
                )
            ),
            $result,
            'Invalid <rootMediaFolder> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTrashTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'trash'
            ),
            $result,
            'Invalid <trash> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTrashTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'trash',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Trash+xml',
                    'href' => '/content/trash'
                )
            ),
            $result,
            'Invalid <trash> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionsTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'sections'
            ),
            $result,
            'Invalid <sections> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'sections',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.SectionList+xml',
                    'href' => '/content/sections'
                )
            ),
            $result,
            'Invalid <sections> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsViewsTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'views'
            ),
            $result,
            'Invalid <views> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsViewsTagAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'views',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RefList+xml',
                    'href' => '/content/views'
                )
            ),
            $result,
            'Invalid <views> tag attributes.',
            false
        );
    }

    /**
     * Get the Role visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Root
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Root;
    }
}
