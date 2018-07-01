<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class BookmarkTest extends RESTFunctionalTestCase
{
    public function testCreateBookmark(): int
    {
        $content = $this->createFolder(__FUNCTION__, '/api/ezp/v2/content/locations/1/2');
        $contentLocations = $this->getContentLocations($content['_href']);

        $locationPathParts = explode('/', $contentLocations['LocationList']['Location'][0]['_href']);
        $locationId = (int) array_pop($locationPathParts);

        $request = $this->createHttpRequest(
            'POST', '/api/ezp/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_CREATED);

        return $locationId;
    }

    /**
     * @depends testCreateBookmark
     */
    public function testCreateBookmarkIfAlreadyExists(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'POST', '/api/ezp/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_CONFLICT);
    }

    /**
     * @depends testCreateBookmark
     */
    public function testIsBookmarked(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'HEAD', '/api/ezp/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_OK);
    }

    public function testIsBookmarkedReturnsNotFound(): void
    {
        $locationId = 43;

        $request = $this->createHttpRequest(
            'HEAD', '/api/ezp/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * @depends testCreateBookmark
     */
    public function testDeleteBookmark(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'DELETE', '/api/ezp/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_NO_CONTENT);
    }

    public function testLoadBookmarks(): void
    {
        $request = $this->createHttpRequest(
            'GET',
            '/api/ezp/v2/bookmark?offset=1&limit=100',
            'BookmarkList+xml',
            'BookmarkList+xml'
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_OK);
    }

    public function testDeleteBookmarkReturnNotFound(): void
    {
        $locationId = 43;

        $request = $this->createHttpRequest(
            'DELETE', '/api/ezp/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
