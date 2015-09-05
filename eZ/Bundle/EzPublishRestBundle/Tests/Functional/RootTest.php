<?php

/**
 * File containing the Functional\RootTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class RootTest extends RESTFunctionalTestCase
{
    /**
     * @covers GET /
     */
    public function testLoadRootResource()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/')
        );
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @dataProvider getRandomUriSet
     * @covers GET /<wrongUri>
     */
    public function testCatchAll($uri)
    {
        self::markTestSkipped('@todo fixme');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/' . uniqid('rest'), '', 'Stuff+json')
        );
        self::assertHttpResponseCodeEquals($response, 404);
        $responseArray = json_decode($response->getContent(), true);
        self::assertArrayHasKey('ErrorMessage', $responseArray);
        self::assertEquals('No such route', $responseArray['ErrorMessage']['errorDescription']);
    }

    public function getRandomUriSet()
    {
        return array(
            array('/api/ezp/v2/randomUri'),
            array('/api/ezp/v2/randomUri/level/two'),
            array('/api/ezp/v2/randomUri/with/arguments?arg=argh'),
        );
    }
}
