<?php

/**
 * File containing the Functional\UrlWildcardTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class UrlWildcardTest extends RESTFunctionalTestCase
{
    /**
     * Covers GET /content/urlwildcards.
     */
    public function testListURLWildcards()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/urlwildcards')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @returns string The created URL wildcard href
     * Covers POST /content/urlwildcards
     */
    public function testCreateUrlWildcard()
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UrlWildcardCreate>
  <sourceUrl>/{$text}/*</sourceUrl>
  <destinationUrl>/destination/url/{1}</destinationUrl>
  <forward>true</forward>
</UrlWildcardCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/urlwildcards',
            'UrlWildcardCreate+xml',
            'UrlWildcard+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @param $urlWildcardHref
     * Covers GET /content/urlwildcards/{urlWildcardId}
     * @depends testCreateUrlWildcard
     */
    public function testLoadUrlWildcard($urlWildcardHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $urlWildcardHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @param $urlWildcardHref
     * @depends testCreateUrlWildcard
     */
    public function testDeleteURLWildcard($urlWildcardHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $urlWildcardHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
