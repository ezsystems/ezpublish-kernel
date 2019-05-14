<?php

/**
 * File containing the Functional\ViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

class ViewTest extends TestCase
{
    /**
     * Covers POST /views.
     */
    public function testViewRequestWithOrStatement()
    {
        $fooRemoteId = md5('View test content foo');
        $barRemoteId = md5('View test content bar');
        $this->createFolder('View test content foo', '/api/ezp/v2/content/locations/1/2', $fooRemoteId);
        $this->createFolder('View test content bar', '/api/ezp/v2/content/locations/1/2', $barRemoteId);

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TitleView</identifier>
  <Query>
    <Filter>
        <OR>
            <ContentRemoteIdCriterion>{$fooRemoteId}</ContentRemoteIdCriterion>
            <ContentRemoteIdCriterion>{$barRemoteId}</ContentRemoteIdCriterion>
        </OR>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/views',
            'ViewInput+xml',
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);
        $responseData = json_decode($response->getBody(), true);

        self::assertEquals(2, $responseData['View']['Result']['count']);
    }

    /**
     * Covers POST /views.
     *
     * @depends testViewRequestWithOrStatement
     */
    public function testViewRequestWithAndStatement()
    {
        $fooRemoteId = md5('View test content foo');
        $barRemoteId = md5('View test content bar');

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TitleView</identifier>
  <Query>
    <Filter>
        <AND>
            <OR>
                <ContentRemoteIdCriterion>{$fooRemoteId}</ContentRemoteIdCriterion>
                <ContentRemoteIdCriterion>{$barRemoteId}</ContentRemoteIdCriterion>
            </OR>
            <ContentRemoteIdCriterion>{$fooRemoteId}</ContentRemoteIdCriterion>
        </AND>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/views',
            'ViewInput+xml',
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);
        $responseData = json_decode($response->getBody(), true);

        self::assertEquals(1, $responseData['View']['Result']['count']);
    }
}
