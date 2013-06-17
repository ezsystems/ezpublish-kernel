<?php
namespace eZ\Publish\Core\Server\Tests\Functional;

use eZ\Publish\Core\REST\Server\Tests\Functional\TestCase as RESTFunctionalTestCase;

class TrashTest extends RESTFunctionalTestCase
{
    /**
     * @covers MOVE /content/locations/{locationPath} Destination:/content/trash
     * @return string A trash item ID
     */
    public function testCreateTrashItem()
    {
        $trashHref = $this->createTrashItem( 'testCreateTrashItem' );
        $this->addCreatedElement( $trashHref );
        return $trashHref;
    }

    /**
     * @covers GET /content/trash
     */
    public function testLoadTrashItems()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/trash" )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateTrashItem
     * @covers GET /content/trash/{trashItemId}
     */
    public function testLoadTrashItem( $trashItemHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", $trashItemHref )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @covers DELETE /content/trash/{trashItemId}
     * @depends testCreateTrashItem
     */
    public function testDeleteTrashItem( $trashItemId )
    {
        // we create a new one, since restore also needs the feature
        $trashItemHref = $this->createTrashItem( $trashItemId );

        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "DELETE", $trashItemHref )
        );

        self::assertHttpResponseCodeEquals( $response, 204 );
    }

    /**
     * @covers MOVE /content/trash/{trashItemId}
     * @depends testCreateTrashItem
     */
    public function testRestoreTrashItem( $trashItemId )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "MOVE", $trashItemId )
        );

        self::assertHttpResponseCodeEquals( $response, 201 );
        self::assertHttpResponseHasHeader( $response, 'Location' );
    }

    /**
     * @covers DELETE /content/trash
     */
    public function testEmptyTrash()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "DELETE", "/api/ezp/v2/content/trash" )
        );

        self::assertHttpResponseCodeEquals( $response, 204 );
    }

    /**
     * Tests that deleting a trashed item will fail
     */
    public function testDeleteTrashedItemFailsWith404()
    {
        self::markTestSkipped( "Makes the DB inconsistent" );

        // create a folder
        $folderArray = $this->createFolder( __FUNCTION__, '/content/locations/1/2' );

        // send its main location to trash
        $folderLocations = $this->getContentLocations( $folderArray['_href'] );
        $this->sendLocationToTrash( $folderLocations['LocationList']['Location'][0]['_href'] );

        // delete the content we created above
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "DELETE", $folderArray['_href'] )
        );

        self::assertHttpResponseCodeEquals( $response, 404 );
    }

    /**
     * Creates a folder, and sends it to trash
     * @return string the trashed item href
     */
    private function createTrashItem( $id )
    {
        $folder = $this->createFolder( $id, '/content/locations/1/2' );
        $folderLocations = $this->getContentLocations( $folder['_href'] );
        return $this->sendLocationToTrash( $folderLocations['LocationList']['Location'][0]['_href'] );
    }

    /**
     * @param $folderLocations
     *
     * @return array|null|string
     */
    private function sendLocationToTrash( $contentHref )
    {
        $trashRequest = $this->createHttpRequest( "MOVE", $contentHref );
        $trashRequest->addHeader( 'Destination: /content/trash' );

        $response = $this->sendHttpRequest( $trashRequest );

        self::assertHttpResponseCodeEquals( $response, 201 );

        $trashHref = $response->getHeader( 'Location' );

        return $trashHref;
    }
}
