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
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\URLWildcard',
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

    /**
     * Test for the create() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateWithOptionalForwardParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcard = $urlWildcardService->create( '/articles/*', '/content/{1}', true );
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'sourceUrl'  =>  '/articles/*',
                'destinationUrl'  =>  '/content/{1}',
                'forward'  =>  true
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the load() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     * @see \eZ\Publish\API\Repository\URLWildcardService::load()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testLoad()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcardId = $urlWildcardService->create( '/articles/*', '/content/{1}', true )->id;

        // Load newly created url wildcard
        $urlWildcard = $urlWildcardService->load( $urlWildcardId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\URLWildcard',
            $urlWildcard
        );

        return $urlWildcard;
    }

    /**
     * Test for the load() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::load()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoad
     */
    public function testLoadSetsPropertiesOnURLWildcard( URLWildcard $urlWildcard )
    {
        $this->assertPropertiesCorrect(
            array(
                'sourceUrl'  =>  '/articles/*',
                'destinationUrl'  =>  '/content/{1}',
                'forward'  =>  true
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the load() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::load()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoad
     */
    public function testLoadThrowsNotFoundException( URLWildcard $urlWildcard )
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // This call will fail with a NotFoundException
        $urlWildcardService->load( 42 );
        /* END: Use Case */
    }

    /**
     * Test for the remove() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoad
     */
    public function testRemove()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcard = $urlWildcardService->create( '/articles/*', '/content/{1}', true );

        // Store wildcard url for later reuse
        $urlWildcardId = $urlWildcard->id;

        // Remove the newly created url wildcard
        $urlWildcardService->remove( $urlWildcard );

        // This call will fail with a NotFoundException
        $urlWildcardService->load( $urlWildcardId );
        /* END: Use Case */
    }

    /**
     * Test for the loadAll() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testLoadAll()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create new url wildcards
        $urlWildcardOne = $urlWildcardService->create( '/articles/*', '/content/{1}', true );
        $urlWildcardTwo = $urlWildcardService->create( '/news/*', '/content/{1}', true );

        // Load all available url wildcards
        $allUrlWildcards = $urlWildcardService->loadAll();
        /* END: Use Case */

        $this->assertEquals(
            array(
                $urlWildcardOne,
                $urlWildcardTwo
            ),
            $allUrlWildcards
        );
    }

    /**
     * Test for the loadAll() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoadAll
     */
    public function testLoadAllWithOffsetParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create new url wildcards
        $urlWildcardOne = $urlWildcardService->create( '/articles/*', '/content/{1}', true );
        $urlWildcardTwo = $urlWildcardService->create( '/news/*', '/content/{1}', true );

        // Load all available url wildcards
        $allUrlWildcards = $urlWildcardService->loadAll( 1 );
        /* END: Use Case */

        $this->assertEquals( array( $urlWildcardTwo ), $allUrlWildcards );
    }

    /**
     * Test for the loadAll() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoadAll
     */
    public function testLoadAllWithOffsetAndLimitParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create new url wildcards
        $urlWildcardOne = $urlWildcardService->create( '/articles/*', '/content/{1}' );
        $urlWildcardTwo = $urlWildcardService->create( '/news/*', '/content/{1}' );

        // Load all available url wildcards
        $allUrlWildcards = $urlWildcardService->loadAll( 0, 1 );
        /* END: Use Case */

        $this->assertEquals( array( $urlWildcardOne ), $allUrlWildcards );
    }

    /**
     * Test for the loadAll() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoadAll
     */
    public function testLoadAllReturnsEmptyArrayByDefault()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Load all available url wildcards
        $allUrlWildcards = $urlWildcardService->loadAll();
        /* END: Use Case */

        $this->assertSame( array(), $allUrlWildcards );
    }

    public function testTranslate()
    {

    }
}