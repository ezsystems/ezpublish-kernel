<?php
/**
 * File containing the URLWildcardServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\Content\URLWildcard;

/**
 * Test case for operations in the URLWildcardService.
 *
 * @see eZ\Publish\API\Repository\URLWildcardService
 */
class URLWildcardServiceTest extends \eZ\Publish\API\Repository\Tests\BaseTest
{
    /**
     * Test for the create() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetURLWildcardService
     */
    public function testCreate()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcard = $urlWildcardService->create( '/articles/*', '/content/{1}' );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\Publish\API\Repository\Values\Content\\URLWildcard',
            $urlWildcard
        );

        return $urlWildcard;
    }

    /**
     * Test for the create() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard
     * @return void
     *
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateSetsIdPropertyOnURLWildcard( URLWildcard $urlWildcard )
    {
        $this->assertNotNull( $urlWildcard->id );
    }

    /**
     * Test for the create() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard
     * @return void
     *
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateSetsPropertiesOnURLWildcard( URLWildcard $urlWildcard )
    {
        $this->assertPropertiesCorrect(
            array(
                'sourceUrl'  =>  '/articles/*',
                'destinationUrl'  =>  '/content/{1}',
                'forward'  =>  false
            ),
            $urlWildcard
        );
    }
}