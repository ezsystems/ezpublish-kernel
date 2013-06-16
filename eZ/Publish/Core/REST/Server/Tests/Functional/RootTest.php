<?php
namespace eZ\Publish\Core\Server\Tests\Functional;

use eZ\Publish\Core\REST\Server\Tests\Functional\TestCase as RESTFunctionalTestCase;

class RootTest extends RESTFunctionalTestCase
{
    /**
     * @covers GET /
     */
    public function testLoadRootResource()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/" )
        );
        self::assertHttpResponseCodeEquals( $response, 200 );
    }
}
