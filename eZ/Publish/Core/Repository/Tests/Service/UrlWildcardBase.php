<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\UrlWildcardBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\API\Repository\Values\Content\URLWildcard,
    eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;

/**
 * Test case for UrlWildcard Service
 */
abstract class UrlWildcardBase extends BaseServiceTest
{
    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::__construct
     */
    public function testConstructor()
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        /** @var $handler \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler */
        $repository = self::getMock( "eZ\\Publish\\API\\Repository\\Repository" );
        $handler = self::getMock( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlWildcard\\Handler" );
        $settings = array( "settings" );

        $service = new \eZ\Publish\Core\Repository\URLWildcardService( $repository, $handler, $settings );

        self::assertAttributeSame( $repository, "repository", $service );
        self::assertAttributeSame( $handler, "urlWildcardHandler", $service );
        self::assertAttributeSame( $settings, "settings", $service );
    }

    /**
     * Test for the load() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::load
     */
    public function testLoad()
    {
        $service = $this->repository->getURLWildcardService();
        $service->create( "fruit/*", "food/{1}", true );

        $urlWildcard = $service->load( 1 );
        self::assertEquals(
            new URLWildcard(
                array(
                    "id" => 1,
                    "sourceUrl" => "fruit/*",
                    "destinationUrl" => "food/{1}",
                    "forward" => true
                )
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the load() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadThrowsNotFoundException()
    {
        $service = $this->repository->getURLWildcardService();

        $service->load( 100 );
    }

    /**
     * @return array
     */
    public function providerForTestCreate()
    {
        return array(
            array( "fruit", "food", true ),
            array( "fruit/*", "food", false ),
            array( "fruit/*", "food/{1}", true ),
            array( "fruit/*/*", "food/{1}", true ),
            array( "fruit/*/*", "food/{2}", true ),
            array( "fruit/*/*", "food/{1}/{2}", true ),
        );
    }

    /**
     * Test for the create() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::create
     * @depends testLoad
     * @dataProvider providerForTestCreate
     */
    public function testCreate( $sourceUrl, $destinationUrl, $forward )
    {
        $service = $this->repository->getURLWildcardService();
        $urlWildcard = $service->create( $sourceUrl, $destinationUrl, $forward );

        self::assertEquals(
            new URLWildcard(
                array(
                    "id" => 1,
                    "sourceUrl" => $sourceUrl,
                    "destinationUrl" => $destinationUrl,
                    "forward" => $forward
                )
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the create() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::create
     * @depends testCreate
     * @depends testLoad
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testCreateWithRollback()
    {
        $service = $this->repository->getURLWildcardService();

        $this->repository->beginTransaction();
        $service->create( "fruit/*", "food/{1}", true );
        $this->repository->rollback();

        $service->load( 1 );
    }

    /**
     * Test for the create() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateThrowsInvalidArgumentException()
    {
        $service = $this->repository->getURLWildcardService();

        $service->create( "fruit/*", "food/{1}", true );
        $service->create( "fruit/*", "food/{1}", true );
    }

    /**
     * @return array
     */
    public function providerForTestCreateThrowsContentValidationException()
    {
        return array(
            array( "fruit", "food/{1}", true ),
            array( "fruit/*", "food/{2}", false ),
            array( "fruit/*/*", "food/{3}", true ),
        );
    }

    /**
     * Test for the create() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @dataProvider providerForTestCreateThrowsContentValidationException
     */
    public function testCreateThrowsContentValidationException( $sourceUrl, $destinationUrl, $forward )
    {
        $service = $this->repository->getURLWildcardService();

        $service->create( $sourceUrl, $destinationUrl, $forward );
    }

    /**
     * Test for the create() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test not implemented: " . __METHOD__ );
    }

    /**
     * Test for the remove() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::remove
     * @depends testCreate
     * @depends testLoad
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testRemove()
    {
        $service = $this->repository->getURLWildcardService();

        $service->create( "fruit/*", "food/{1}", true );
        $urlWildcard = $service->load( 1 );
        $service->remove( $urlWildcard );

        $service->load( 1 );
    }

    /**
     * Test for the remove() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::remove
     * @depends testRemove
     */
    public function testRemoveWithRollback()
    {
        $service = $this->repository->getURLWildcardService();

        $service->create( "fruit/*", "food/{1}", true );
        $urlWildcard = $service->load( 1 );

        $this->repository->beginTransaction();
        $service->remove( $urlWildcard );
        $this->repository->rollback();

        self::assertEquals(
            $urlWildcard,
            $service->load( 1 )
        );
    }

    /**
     * Test for the remove() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::remove
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemoveThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test not implemented: " . __METHOD__ );
    }

    /**
     * Test for the loadAll() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::loadAll
     * @depends testCreate
     */
    public function testLoadAll()
    {
        $service = $this->repository->getURLWildcardService();
        $service->create( "fruit/*", "food/{1}", true );
        $service->create( "vegetable/*", "food/{1}", true );

        $urlWildcard = $service->loadAll();
        self::assertEquals(
            array(
                new URLWildcard(
                    array(
                        "id" => 1,
                        "sourceUrl" => "fruit/*",
                        "destinationUrl" => "food/{1}",
                        "forward" => true
                    )
                ),
                new URLWildcard(
                    array(
                        "id" => 2,
                        "sourceUrl" => "vegetable/*",
                        "destinationUrl" => "food/{1}",
                        "forward" => true
                    )
                ),
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the loadAll() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::loadAll
     * @depends testCreate
     */
    public function testLoadAllWithOffset()
    {
        $service = $this->repository->getURLWildcardService();
        $service->create( "fruit/*", "food/{1}", true );
        $service->create( "vegetable/*", "food/{1}", true );
        $service->create( "seed/*", "food/{1}", true );

        $urlWildcard = $service->loadAll( 1 );
        self::assertEquals(
            array(
                new URLWildcard(
                    array(
                        "id" => 2,
                        "sourceUrl" => "vegetable/*",
                        "destinationUrl" => "food/{1}",
                        "forward" => true
                    )
                ),
                new URLWildcard(
                    array(
                        "id" => 3,
                        "sourceUrl" => "seed/*",
                        "destinationUrl" => "food/{1}",
                        "forward" => true
                    )
                ),
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the loadAll() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::loadAll
     * @depends testCreate
     */
    public function testLoadAllWithOffsetAndLimit()
    {
        $service = $this->repository->getURLWildcardService();
        $service->create( "fruit/*", "food/{1}", true );
        $service->create( "vegetable/*", "food/{1}", true );
        $service->create( "seed/*", "food/{1}", true );

        $urlWildcard = $service->loadAll( 1, 1 );
        self::assertEquals(
            array(
                new URLWildcard(
                    array(
                        "id" => 2,
                        "sourceUrl" => "vegetable/*",
                        "destinationUrl" => "food/{1}",
                        "forward" => true
                    )
                ),
            ),
            $urlWildcard
        );
    }

    /**
     * @return array
     */
    public function providerForTestTranslate()
    {
        return array(
            array( "" ),
        );
    }

    /**
     * Test for the translate() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::translate
     * @dataProvider providerForTestTranslate
     */
    public function testTranslate( $url )
    {
        $this->markTestIncomplete( "Test not implemented: " . __METHOD__ );
        $service = $this->repository->getURLWildcardService();

    }

    /**
     * Test for the translate() method.
     *
     * @covers \eZ\Publish\Core\Repository\UrlWildcardService::translate
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testTranslateThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test not implemented: " . __METHOD__ );
        $service = $this->repository->getURLWildcardService();

    }
}
