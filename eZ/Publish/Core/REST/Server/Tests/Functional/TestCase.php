<?php
namespace eZ\Publish\Core\REST\Server\Tests\Functional;

use \Buzz\Message\Request as HttpRequest;
use \Buzz\Message\Response as HttpResponse;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Buzz\Client\ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $httpHost;

    /**
     * @var string
     * Basic auth login:password
     */
    private $httpAuth;

    protected static $testSuffix;

    protected function setUp()
    {
        parent::setUp();

        if ( !$this->httpHost = getenv( 'EZP_TEST_REST_HOST' ) )
        {
            self::markTestSkipped( "Set the EZP_TEST_REST_HOST (api.example.com) to your eZ Publish test instance" );
        }

        if ( !$this->httpAuth = getenv( 'EZP_TEST_REST_AUTH' ) )
        {
            self::markTestSkipped( "Set the EZP_TEST_REST_AUTH (admin:publish) to your eZ Publish test instance" );

        }

        $this->httpClient = new \Buzz\Client\Curl();
        $this->httpClient->setVerifyPeer( false );
        $this->httpClient->setTimeout( 90 );
        $this->httpClient->setOption( CURLOPT_FOLLOWLOCATION, false );
    }

    /**
     * @return HttpResponse
     */
    public function sendHttpRequest( HttpRequest $request )
    {
        $response = new HttpResponse;
        $this->httpClient->send( $request, $response );
        return $response;
    }

    /**
     * @return HttpRequest
     */
    public function createHttpRequest( $method, $uri, $contentType = '', $acceptType = '' )
    {
        $request = new HttpRequest( $method, $uri, $this->httpHost );
        $request->addHeader( 'Authorization: Basic ' . base64_encode( $this->httpAuth ) );
        $request->addHeader( "Content-Type: " . $this->generateMediaTypeString( $contentType ) );
        $request->addHeader( "Accept: " . $this->generateMediaTypeString( $acceptType ) );
        return $request;
    }

    protected function assertHttpResponseCodeEquals( HttpResponse $response, $expected )
    {
        $responseCode = $response->getStatusCode();
        if ( $responseCode != $expected )
        {
            $errorMessageString = '';
            if ( $response->getHeader( 'Content-Type' ) == 'application/vnd.ez.api.ErrorMessage+xml' )
            {
                $body = \simplexml_load_string( $response->getContent() );
                $errorMessageString = $body->errorDescription;
            }
            elseif ( ( $response->getHeader( 'Content-Type' ) == 'application/vnd.ez.api.ErrorMessage+json' ) )
            {
                $body = json_decode( $response->getContent() );
                $errorMessageString =  "Error message: {$body->ErrorMessage->errorDescription}";
            }

            self::assertEquals( $expected, $responseCode, $errorMessageString );
        }
    }

    protected function assertHttpResponseHasHeader( HttpResponse $response, $header, $expectedValue = null )
    {
        $header = $response->getHeader( $header );
        self::assertNotNull( $header, "Response has a $response header" );
        if ( $expectedValue !== null )
        {
            self::assertEquals( $header, $expectedValue );
        }
    }

    protected function generateMediaTypeString( $typeString )
    {
        return "application/vnd.ez.api.$typeString";
    }

    protected function addCreatedElement( $href )
    {
        $testCase = $this;
        self::$createdContent[$href] = function() use ( $href, $testCase )
        {
            $response = $testCase->sendHttpRequest(
                $testCase->createHttpRequest( 'DELETE', $href )
            );
        };
    }

    public static function tearDownAfterClass()
    {
        self::clearCreatedContent( self::$createdContent );
    }

    private static function clearCreatedContent( array $contentArray )
    {
        foreach ( array_reverse( $contentArray ) as $contentId => $callback )
        {
            $callback();
        }
    }

    /**
     * List of REST contentId (/content/objects/12345) created by tests
     * @var array
     */
    private static $createdContent = array();

    /**
     * @param string $parentLocationId The REST id of the parent location
     * @return array created Content, as an array
     */
    protected function createFolder( $text, $parentLocationId )
    {
        if ( !isset( self::$testSuffix ) )
        {
            self::$testSuffix = uniqid();
        }

        $text = $text . "_" . self::$testSuffix;
        $xml = <<< XML
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

        return $this->createContent( $xml );
    }

    /**
     * @param $xml
     *
     * @return array Content key of the Content struct array
     */
    protected function createContent( $xml )
    {
        $request = $this->createHttpRequest( "POST", "/api/ezp/v2/content/objects", "ContentCreate+xml", "Content+json" );
        $request->setContent( $xml );

        $response = $this->sendHttpRequest( $request );

        $content = json_decode( $response->getContent(), true );

        $this->sendHttpRequest(
            $request = $this->createHttpRequest( "PUBLISH", $content['Content']['CurrentVersion']['Version']['_href'] )
        );

        $this->addCreatedElement( $content['Content']['_href'], true );

        return $content['Content'];
    }

    /**
     * Converts a REST href to an ID
     * @param string $href
     * @return string
     */
    protected function hrefToId( $href )
    {
        return str_replace( '/api/ezp/v2', '', $href );
    }
}
