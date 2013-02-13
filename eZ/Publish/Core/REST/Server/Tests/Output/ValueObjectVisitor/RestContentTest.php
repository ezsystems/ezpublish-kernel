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
use eZ\Publish\Core\REST\Server\Values\RestContent;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestContentTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitWithoutEmbeddedVersion()
    {
        $visitor   = $this->getContentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $restContent = $this->getBasicRestContent();

        $this->getVisitorMock()->expects( $this->never() )
            ->method( 'visitValueObject' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContent
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    protected function getBasicRestContent()
    {
        return new RestContent(
            new ContentInfo(
                array(
                    'id' => 'content23',
                    'name' => 'Sindelfingen',
                    'sectionId' => 'section23',
                    'currentVersionNo' => 5,
                    'published' => true,
                    'ownerId' => 'user23',
                    'modificationDate' => new \DateTime( '2012-09-05 15:27 Europe/Berlin' ),
                    'publishedDate' => null,
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
                )
            ),
            null
        );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content[@href="/content/objects/content23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentIdCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content[@id="content23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentMediaTypeWithoutVersionCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content[@media-type="application/vnd.ez.api.ContentInfo+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentRemoteIdCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content[@remoteId="abc123"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentTypeHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/ContentType[@href="/content/types/contentType23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentTypeMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/ContentType[@media-type="application/vnd.ez.api.ContentType+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Name[text()="Sindelfingen"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Versions[@href="/content/objects/content23/versions"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Versions[@media-type="application/vnd.ez.api.VersionList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/CurrentVersion[@href="/content/objects/content23/currentversion"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/CurrentVersion[@media-type="application/vnd.ez.api.Version+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Section[@href="/content/sections/section23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Section[@media-type="application/vnd.ez.api.Section+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/MainLocation[@href="/content/locations/1/2/23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/MainLocation[@media-type="application/vnd.ez.api.Location+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Locations[@href="/content/objects/content23/locations"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Locations[@media-type="application/vnd.ez.api.LocationList+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Owner[@href="/user/users/user23"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/Owner[@media-type="application/vnd.ez.api.User+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/mainLanguageCode[text()="eng-US"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/alwaysAvailable[text()="true"]'  );
    }

    /**
     * @return \DOMDocument
     */
    public function testVisitWithEmbeddedVersion()
    {
        $visitor   = $this->getContentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $restContent = $this->getBasicRestContent();
        $restContent->currentVersion = new Values\Content\Content(
            array(
                'internalFields' => array()
            )
        );
        $restContent->relations = array();
        $restContent->contentType = $this->getMockForAbstractClass(
            "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType"
        );

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'visitValueObject' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\REST\\Server\\Values\\Version' ) );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContent
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
     * @depends testVisitWithEmbeddedVersion
     */
    public function testContentMediaTypeWithVersionCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content[@media-type="application/vnd.ez.api.Content+xml"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testEmbeddedCurrentVersionHrefCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/CurrentVersion[@href="/content/objects/content23/currentversion"]'  );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testEmbeddedCurrentVersionMediaTypeCorrect( \DOMDocument $dom )
    {
        $this->assertXPath( $dom, '/Content/CurrentVersion[@media-type="application/vnd.ez.api.Version+xml"]'  );
    }

    /**
     * Get the Content visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestContent
     */
    protected function getContentVisitor()
    {
        return new ValueObjectVisitor\RestContent(
            new Common\UrlHandler\eZPublish()
        );
    }
}
