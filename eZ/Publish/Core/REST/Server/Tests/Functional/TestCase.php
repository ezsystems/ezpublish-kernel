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
            $body = json_decode( $response->getContent() );
            $errorMessageString =  ( ( $body instanceof \StdClass ) && isset( $body->ErrorMessage ) )
                ? ". Error message: {$body->ErrorMessage->errorDescription}"
                : null;

            self::assertEquals( $expected, $responseCode, $errorMessageString );
        }
    }

    protected function assertHttpResponseHasHeader( HttpResponse $response, $header, $expectedValue = null )
    {
        $header = $response->getHeader( $header );
        self::assertNotNull( $header, "Response has a $response header" );
        if ( $expectedValue !== null )
        {
            self::assertEquals( $header, $expectedValue, "Header $header matches the expected value" );
        }
    }

    protected function generateMediaTypeString( $typeString )
    {
        return "application/vnd.ez.api.$typeString";
    }

    protected function addCreatedContent( $contentId )
    {
        $testCase =& $this;
        self::$createdContent[$contentId] = function() use ( $contentId, $testCase )
        {
            $response = $testCase->sendHttpRequest(
                $testCase->createHttpRequest( 'DELETE', "/api/ezp/v2/content/objects/{$contentId}" )
            );
        };
    }

    public static function tearDownAfterClass()
    {
        self::clearCreatedContent( self::$createdContent );
    }

    private static function clearCreatedContent( array $contentArray )
    {
        foreach ( $contentArray as $contentId => $callback )
        {
            $callback();
        }
    }

    /**
     * List of REST contentId (/content/objects/12345) created by tests
     * @var array
     */
    private static $createdContent = array();
}
