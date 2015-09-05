<?php

/**
 * File containing the Functional\TrashTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class TrashTest extends RESTFunctionalTestCase
{
    /**
     * @return string The created trash item href
     */
    public function testCreateTrashItem()
    {
        return $this->createTrashItem('testCreateTrashItem');
    }

    /**
     * @covers GET /content/trash
     */
    public function testLoadTrashItems()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/trash')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateTrashItem
     * @covers GET /content/trash/{trashItemId}
     */
    public function testLoadTrashItem($trashItemHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $trashItemHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers DELETE /content/trash/{trashItemId}
     * @depends testCreateTrashItem
     */
    public function testDeleteTrashItem($trashItemId)
    {
        // we create a new one, since restore also needs the feature
        $trashItemHref = $this->createTrashItem($trashItemId);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $trashItemHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @covers MOVE /content/trash/{trashItemId}
     * @depends testCreateTrashItem
     */
    public function testRestoreTrashItem($trashItemId)
    {
        self::markTestSkipped('@todo fixme');

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('MOVE', $trashItemId)
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * @covers MOVE /content/trash/{trashItemId} Destination:/content/locations/{locationPath}
     */
    public function testRestoreTrashItemWithDestination()
    {
        $trashItemHref = $this->createTrashItem(__FUNCTION__);

        $request = $this->createHttpRequest('MOVE', $trashItemHref);
        $request->addHeader('Destination: /api/ezp/v2/content/locations/1/2');

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * @covers DELETE /content/trash
     */
    public function testEmptyTrash()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', '/api/ezp/v2/content/trash')
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Tests that deleting a trashed item will fail.
     */
    public function testDeleteTrashedItemFailsWith404()
    {
        self::markTestSkipped('Makes the DB inconsistent');

        // create a folder
        $folderArray = $this->createFolder(__FUNCTION__, '/api/ezp/v2/content/locations/1/2');

        // send its main location to trash
        $folderLocations = $this->getContentLocations($folderArray['_href']);
        $this->sendLocationToTrash($folderLocations['LocationList']['Location'][0]['_href']);

        // delete the content we created above
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $folderArray['_href'])
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * Creates a folder, and sends it to trash.
     *
     * @return string the trashed item href
     */
    private function createTrashItem($id)
    {
        $folder = $this->createFolder($id, '/api/ezp/v2/content/locations/1/2');
        $folderLocations = $this->getContentLocations($folder['_href']);

        return $this->sendLocationToTrash($folderLocations['LocationList']['Location'][0]['_href']);
    }

    /**
     * @param $folderLocations
     *
     * @return array|null|string
     */
    private function sendLocationToTrash($contentHref)
    {
        $trashRequest = $this->createHttpRequest('MOVE', $contentHref);
        $trashRequest->addHeader('Destination: /api/ezp/v2/content/trash');

        $response = $this->sendHttpRequest($trashRequest);

        self::assertHttpResponseCodeEquals($response, 201);

        $trashHref = $response->getHeader('Location');

        return $trashHref;
    }
}
