<?php
/**
 * File containing the URLWildcardServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;

/**
 * Test case for operations in the URLWildcardService.
 *
 * @see eZ\Publish\API\Repository\URLWildcardService
 * @group url-wildcard
 */
class URLWildcardServiceTest extends BaseTest
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
     *
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
     *
     * @return void
     *
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateSetsPropertiesOnURLWildcard( URLWildcard $urlWildcard )
    {
        $this->assertPropertiesCorrect(
            array(
                'sourceUrl' => '/articles/*',
                'destinationUrl' => '/content/{1}',
                'forward' => false
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
                'sourceUrl' => '/articles/*',
                'destinationUrl' => '/content/{1}',
                'forward' => true
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the create() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateThrowsInvalidArgumentExceptionOnDuplicateSourceUrl()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcardService->create( '/articles/*', '/content/{1}', true );

        // This call will fail with an InvalidArgumentException because the
        // sourceUrl '/articles/*' already exists.
        $urlWildcardService->create( '/articles/*', '/content/data/{1}' );
        /* END: Use Case */
    }

    /**
     * Test for the create() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateThrowsContentValidationExceptionWhenPatternsAndPlaceholdersNotMatch()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // This call will fail with a ContentValidationException because the
        // number of patterns '*' does not match the number of {\d} placeholders
        $urlWildcardService->create( '/articles/*', '/content/{1}/year{2}' );
        /* END: Use Case */
    }

    /**
     * Test for the create() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateThrowsContentValidationExceptionWhenPlaceholdersNotValidNumberSequence()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // This call will fail with a ContentValidationException because the
        // number of patterns '*' does not match the number of {\d} placeholders
        $urlWildcardService->create( '/articles/*/*/*', '/content/{1}/year/{2}/{4}' );
        /* END: Use Case */
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
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::load()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testLoad
     */
    public function testLoadSetsPropertiesOnURLWildcard( URLWildcard $urlWildcard )
    {
        $this->assertPropertiesCorrect(
            array(
                'sourceUrl' => '/articles/*',
                'destinationUrl' => '/content/{1}',
                'forward' => true
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the load() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $urlWildcard
     *
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
     * @see \eZ\Publish\API\Repository\URLWildcardService::loadAll()
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
     * @see \eZ\Publish\API\Repository\URLWildcardService::loadAll()
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
     * @see \eZ\Publish\API\Repository\URLWildcardService::loadAll()
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
     * @see \eZ\Publish\API\Repository\URLWildcardService::loadAll()
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

    /**
     * Test for the translate() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult
     * @see \eZ\Publish\API\Repository\URLWildcardService::translate()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testTranslate()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcardService->create( '/articles/*', '/content/{1}' );

        // Translate a given url
        $result = $urlWildcardService->translate( '/articles/2012/05/sindelfingen' );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\URLWildcardTranslationResult',
            $result
        );

        return $result;
    }

    /**
     * Test for the translate() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult $result
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::translate()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testTranslate
     */
    public function testTranslateSetsPropertiesOnTranslationResult( URLWildcardTranslationResult $result )
    {
        $this->assertPropertiesCorrect(
            array(
                'uri' => '/content/2012/05/sindelfingen',
                'forward' => false
            ),
            $result
        );
    }

    /**
     * Test for the translate() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::translate()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testTranslate
     */
    public function testTranslateWithForwardSetToTrue()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcardService->create( '/articles/*/05/*', '/content/{2}/year/{1}', true );

        // Translate a given url
        $result = $urlWildcardService->translate( '/articles/2012/05/sindelfingen' );
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'uri' => '/content/sindelfingen/year/2012',
                'forward' => true
            ),
            $result
        );
    }

    /**
     * Test for the translate() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::translate()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testTranslate
     */
    public function testTranslateReturnsLongestMatchingWildcard()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // Create new url wildcards
        $urlWildcardService->create( '/articles/*/05/*', '/content/{2}/year/{1}' );
        $urlWildcardService->create( '/articles/*/05/sindelfingen/*', '/content/{2}/bar/{1}' );

        // Translate a given url
        $result = $urlWildcardService->translate( '/articles/2012/05/sindelfingen/42' );
        /* END: Use Case */

        $this->assertEquals( '/content/42/bar/2012', $result->uri );
    }

    /**
     * Test for the translate() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     * @see \eZ\Publish\API\Repository\URLWildcardService::translate()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testTranslate
     */
    public function testTranslateThrowsNotFoundExceptionWhenNotAliasOrWildcardMatches()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlWildcardService = $repository->getURLWildcardService();

        // This call will fail with a NotFoundException because no wildcard or
        // url alias matches against the given url.
        $urlWildcardService->translate( '/sindelfingen' );
        /* END: Use Case */
    }
}
