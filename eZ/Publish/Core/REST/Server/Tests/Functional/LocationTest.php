<?php
namespace eZ\Publish\Core\Server\Tests\Functional;

use eZ\Publish\Core\REST\Server\Tests\Functional\TestCase as RESTFunctionalTestCase;

class LocationTest extends RESTFunctionalTestCase
{
    private static $testSuffix;

    /**
     * @covers POST /content/objects/{contentId}/locations
     * @returns string location href
     */
    public function testCreateLocation()
    {
        $content = $this->createFolder( 'testCreateLocation', '/content/locations/1/2' );
        $contentHref = $content['_href'];

        $remoteId = "testCreatelocation_" . self::$testSuffix;

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<LocationCreate>
  <ParentLocation href="/content/locations/1/43" />
  <remoteId>{$remoteId}</remoteId>
  <priority>0</priority>
  <hidden>false</hidden>
  <sortField>PATH</sortField>
  <sortOrder>ASC</sortOrder>
</LocationCreate>
XML;
        $request = $this->createHttpRequest( "POST", "$contentHref/locations", "LocationCreate+xml", "Location+json" );
        $request->setContent( $body );

        $response = $this->sendHttpRequest( $request );
        self::assertHttpResponseCodeEquals( $response, 201 );
        self::assertHttpResponseHasHeader( $response, 'Location' );

        $href = $response->getHeader( 'Location' );
        return $href;
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/locations?remoteId=<locationRemoteId>
     */
    public function testRedirectLocationByRemoteId( $locationHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/locations?remoteId=testCreateLocation_" . self::$testSuffix )
        );

        self::assertHttpResponseCodeEquals( $response, 307 );
        self::assertHttpResponseHasHeader( $response, 'Location', $locationHref );
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/locations?id=<locationId>
     */
    public function testRedirectLocationById( $locationHref )
    {
        $id = array_pop( explode( '/', $locationHref ) );
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/locations?id=$id" )
        );

        self::assertHttpResponseCodeEquals( $response, 307 );
        self::assertHttpResponseHasHeader( $response, 'Location', $locationHref );
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/locations/{locationPath}
     */
    public function testLoadLocation( $locationHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( 'GET', $locationHref )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateLocation
     * @covers COPY /content/locations/{locationPath}
     * @return string the created location's href
     */
    public function testCopySubtree( $locationHref )
    {
        $request = $this->createHttpRequest( 'COPY', $locationHref );
        $request->addHeader( 'Destination: /content/locations/1/43' );
        $response = $this->sendHttpRequest( $request );

        self::assertHttpResponseCodeEquals( $response, 201 );
        self::assertHttpResponseHasHeader( $response, 'Location' );

        return $response->getHeader( 'Location' );
    }

    /**
     * @covers MOVE /content/locations/{locationPath}
     * @depends testCopySubtree
     */
    public function testMoveSubtree( $locationHref )
    {
        $request = $this->createHttpRequest( 'MOVE', $locationHref );
        $request->addHeader( 'Destination: /content/locations/1/5' );
        $response = $this->sendHttpRequest( $request );

        self::assertHttpResponseCodeEquals( $response, 201 );
        self::assertHttpResponseHasHeader( $response, 'Location' );
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/objects/{contentId}/locations
     */
    public function testLoadLocationsForContent( $contentHref)
    {

    }

    /**
     * @depends testCreateLocation
     * @covers SWAP /content/locations/{locationPath}
     */
    public function testSwapLocation( $locationHref )
    {
        self::markTestSkipped( "@todo Implement" );

        /*$content = $this->createFolder( __FUNCTION__, "/content/locations/1/2" );

        $request = $this->createHttpRequest( 'SWAP', $locationHref );
        $request->addHeader( "Destination: $newFolderHref" );

        $response = $this->sendHttpRequest( $request );
        self::assertHttpResponseCodeEquals( $response, 204 );*/
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/locations/{locationPath}/children
     */
    public function testLoadLocationChildren( $locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "$locationHref/children", '', 'LocationList+json' )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
        self::assertHttpResponseHasHeader( $response, 'Content-Type', $this->generateMediaTypeString( 'LocationList+json' ) );
    }

    /**
     * @covers PATCH /content/locations/{locationPath}
     * @depends testCreateLocation
     */
    public function testUpdateLocation( $locationHref )
    {
        $body = <<< XML
<LocationUpdate>
  <priority>3</priority>
  <sortField>PATH</sortField>
  <sortOrder>ASC</sortOrder>
</LocationUpdate>
XML;

        $request = $this->createHttpRequest( 'PATCH', $locationHref, 'LocationUpdate+xml', 'Location+json' );
        $request->setContent( $body );

        $response = $this->sendHttpRequest( $request );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateLocation
     * @covers DELETE /content/locations/{path}
     */
    public function testDeleteSubtree( $locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( 'DELETE', $locationHref )
        );

        self::assertHttpResponseCodeEquals( $response, 204 );
    }

    /**
     * @param string $parentLocationId The REST id of the parent location
     * @return array created Content, as an array
     */
    private function createFolder( $text, $parentLocationId )
    {
        if ( !isset( self::$testSuffix ) )
        {
            self::$testSuffix = uniqid();
        }

        $text = $text . "_" . self::$testSuffix;
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/content/types/1" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="{$parentLocationId}" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>{$text}</remoteId>
  <User href="/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$text}</fieldValue>
    </field>
  </fields>
</ContentCreate>
XML;

        $request = $this->createHttpRequest( "POST", "/api/ezp/v2/content/objects", "ContentCreate+xml", "Content+json" );
        $request->setContent( $body );

        $response = $this->sendHttpRequest( $request );

        $content = json_decode( $response->getContent(), true );

        $this->sendHttpRequest(
            $request = $this->createHttpRequest( "PUBLISH", $content['Content']['CurrentVersion']['Version']['_href'] )
        );

        $this->addCreatedElement( $content['Content']['_href'], true );
        return $content['Content'];
    }
}
