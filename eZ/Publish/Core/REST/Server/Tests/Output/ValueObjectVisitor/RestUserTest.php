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
use eZ\Publish\Core\REST\Server\Values\RestUser;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestUserTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitWithoutEmbeddedVersion()
    {
        $visitor   = $this->getUserVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $restUser = $this->getBasicRestUser();

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'visitValueObject' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restUser
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    protected function getBasicRestUser()
    {
        return new RestUser(
            new Values\User\User(),
            $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" ),
            new ContentInfo(
                array(
                    'id' => 'content23',
                    'name' => 'Sindelfingen',
                    'sectionId' => 'section23',
                    'currentVersionNo' => 5,
                    'published' => true,
                    'ownerId' => 'user23',
                    'modificationDate' => new \DateTime( '2012-09-05 15:27 Europe/Berlin' ),
                    'publishedDate' => new \DateTime( '2012-09-05 15:27 Europe/Berlin' ),
                    'alwaysAvailable' => true,
                    'remoteId' => 'abc123',
                    'mainLanguageCode' => 'eng-US',
                    'mainLocationId' => 'location23',
                    'contentTypeId' => 'contentType23',
                )
            ),
            new Values\Content\Location(
                array(
                    'pathString' => '/1/2/23',
                    'path' => array( 1, 2, 23 )
                )
            ),
            array()
        );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User[@href="/user/users/content23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserIdCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User[@id="content23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserMediaTypeWithoutVersionCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User[@media-type="application/vnd.ez.api.User+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserRemoteIdCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User[@remoteId="abc123"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserTypeHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/ContentType[@href="/content/types/contentType23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserTypeMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/ContentType[@media-type="application/vnd.ez.api.ContentType+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/name[text()="Sindelfingen"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Versions[@href="/content/objects/content23/versions"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Versions[@media-type="application/vnd.ez.api.VersionList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Section[@href="/content/sections/section23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Section[@media-type="application/vnd.ez.api.Section+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/MainLocation[@href="/content/locations/1/2/23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/MainLocation[@media-type="application/vnd.ez.api.Location+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Locations[@href="/content/objects/content23/locations"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Locations[@media-type="application/vnd.ez.api.LocationList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Owner[@href="/user/users/user23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Owner[@media-type="application/vnd.ez.api.User+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/mainLanguageCode[text()="eng-US"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/alwaysAvailable[text()="true"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupsHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/UserGroups[@href="/user/users/content23/groups"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupsMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/UserGroups[@media-type="application/vnd.ez.api.UserGroupList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Roles[@href="/user/users/content23/roles"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/User/Roles[@media-type="application/vnd.ez.api.RoleAssignmentList+xml"]'  );
    }

    /**
     * Get the User visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestUser
     */
    protected function getUserVisitor()
    {
        return new ValueObjectVisitor\RestUser(
            new Common\UrlHandler\eZPublish()
        );
    }
}
