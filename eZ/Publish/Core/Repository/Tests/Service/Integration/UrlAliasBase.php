<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\UrlAliasBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\Content\UrlAlias;
use eZ\Publish\SPI\Persistence\Content\UrlAlias as SPIUrlAlias;
use eZ\Publish\Core\Repository\Values\Content\Location;

/**
 * Test case for UrlAlias Service
 */
abstract class UrlAliasBase extends BaseServiceTest
{
    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupRootLocation()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-d41d8cd98f00b204e9800998ecf8427e",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 2,
                    "path" => "/",
                    "languageCodes" => array( "eng-US", "eng-GB" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupAlwaysAvailable()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "Users" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-9bc65c2abec141778ffaa729489f3e87",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 5,
                    "path" => "/Users",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupAlwaysAvailableAlwaysFound()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "ger-DE" ),
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $urlAlias = $urlAliasService->lookup( "Users" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-9bc65c2abec141778ffaa729489f3e87",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 5,
                    "path" => "/Users",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookupThrowsNotFoundExceptionUrl()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAliasService->lookup( "jabberwocky" );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookup()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "Media/Multimedia" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "9-2e5bc8831f7ae6a29530e7f1bbf2de9c",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 53,
                    "path" => "/Media/Multimedia",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupCaseInsensitive()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "MEDIA/MULTIMEDIA" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "9-2e5bc8831f7ae6a29530e7f1bbf2de9c",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 53,
                    "path" => "/Media/Multimedia",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupWithLanguageCode()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "Media/Multimedia", "eng-US" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "9-2e5bc8831f7ae6a29530e7f1bbf2de9c",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 53,
                    "path" => "/Media/Multimedia",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookupThrowsNotFoundExceptionTranslation()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAliasService->lookup( "Design/Plain-site", "eng-GB" );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupWithShowAllTranslations()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "ger-DE" ),
            "showAllTranslations" => true,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $urlAlias = $urlAliasService->lookup( "Media/Multimedia" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "9-2e5bc8831f7ae6a29530e7f1bbf2de9c",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 53,
                    "path" => "/Media/Multimedia",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::lookup
     */
    public function testLookupHistory()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup( "Users/Guest-accounts" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "2-e57843d836e3af8ab611fde9e2139b3a",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 12,
                    "path" => "/Users/Guest-accounts",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::reverseLookup
     */
    public function testReverseLookup()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array(
                "eng-US"
            ),
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $location = $this->getLocationStub( 53 );
        $urlAlias = $urlAliasService->reverseLookup( $location );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "9-2e5bc8831f7ae6a29530e7f1bbf2de9c",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 53,
                    "path" => "/Media/Multimedia",
                    "languageCodes" => array( "eng-US" ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::reverseLookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testReverseLookupThrowsNotFoundException()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array(
                "ger-DE"
            ),
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $location = $this->getLocationStub( 56 );
        $urlAliasService->reverseLookup( $location );
    }

    public function providerForTestCreateUrlAlias()
    {
        return array(
            array( "my/custom/alias", true, true ),
            array( "/my/custom/alias ", false, false ),
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::createUrlAlias
     * @dataProvider providerForTestCreateUrlAlias
     */
    public function testCreateUrlAlias( $path, $forwarding, $alwaysAvailable )
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $location = $this->getLocationStub( 53 );
        $urlAlias = $urlAliasService->createUrlAlias( $location, $path, "eng-GB", $forwarding, $alwaysAvailable );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "42-724874d1be77f450a09b305fc1534afb",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 53,
                    "path" => "/my/custom/alias",
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => $alwaysAvailable,
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => $forwarding,
                )
            ),
            $urlAlias
        );

        self::assertEquals(
            $urlAlias,
            $urlAliasService->lookup( "my/custom/alias" )
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::createUrlAlias
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateUrlAliasThrowsInvalidArgumentException()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $location = $this->getLocationStub( 53 );
        $urlAliasService->createUrlAlias( $location, "some/path", "eng-GB", true, true );
        $urlAliasService->createUrlAlias( $location, "some/path", "eng-GB", true, true );
    }

    public function providerForTestCreateGlobalUrlAlias()
    {
        return array(
            array( "my/global/alias", true, true ),
            array( "/my/global/alias ", false, false ),
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::createGlobalUrlAlias
     * @dataProvider providerForTestCreateGlobalUrlAlias
     */
    public function testCreateGlobalUrlAlias( $path, $forwarding, $alwaysAvailable )
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAlias = $urlAliasService->createGlobalUrlAlias( "module:content/search", $path, "eng-GB", $forwarding, $alwaysAvailable );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "42-724874d1be77f450a09b305fc1534afb",
                    "type" => UrlAlias::RESOURCE,
                    "destination" => "content/search",
                    "path" => "/my/global/alias",
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => $alwaysAvailable,
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => $forwarding,
                )
            ),
            $urlAlias
        );

        self::assertEquals(
            $urlAlias,
            $urlAliasService->lookup( "my/global/alias" )
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::createGlobalUrlAlias
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateGlobalUrlAliasThrowsInvalidArgumentExceptionPath()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAliasService->createGlobalUrlAlias( "module:content/search", "some/path", "eng-GB", true, true );
        $urlAliasService->createGlobalUrlAlias( "module:content/search", "some/path", "eng-GB", true, true );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::createGlobalUrlAlias
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateGlobalUrlAliasThrowsInvalidArgumentExceptionResource()
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $urlAliasService->createGlobalUrlAlias( "invalid/resource", "some/path", "eng-GB", true, true );
    }

    /**
     * Test for the listGlobalAliases(() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::listGlobalAliases
     */
    public function testListGlobalAliases()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $count = 4;

        for ( $i = 0; $i < $count; $i++ )
        {
            $urlAliasService->createGlobalUrlAlias( "module:content/search", "my/global/alias{$i}", "eng-GB", true, true );
        }

        $urlAliases = $urlAliasService->listGlobalAliases();

        self::assertCount( $count, $urlAliases );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                new UrlAlias(
                    array(
                        "id" => "42-" . md5( "alias{$index}" ),
                        "type" => UrlAlias::RESOURCE,
                        "destination" => "content/search",
                        "path" => "/my/global/alias" . $index,
                        "languageCodes" => array( "eng-GB" ),
                        "alwaysAvailable" => true,
                        "isHistory" => false,
                        "isCustom" => true,
                        "forward" => true,
                    )
                ),
                $urlAlias
            );
        }
    }

    /**
     * Test for the listGlobalAliases(() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::listGlobalAliases
     */
    public function testListGlobalAliasesWithLanguageCode()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $count = 4;
        $countFirst = 2;

        for ( $i = 0; $i < $count; $i++ )
        {
            $languageCode = $countFirst > $i ? "eng-GB" : "eng-US";
            $urlAliasService->createGlobalUrlAlias( "module:content/search", "my/global/alias{$i}", $languageCode, true, true );
        }

        $urlAliases = $urlAliasService->listGlobalAliases( "eng-GB" );

        self::assertCount( $countFirst, $urlAliases );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                new UrlAlias(
                    array(
                        "id" => "42-" . md5( "alias{$index}" ),
                        "type" => UrlAlias::RESOURCE,
                        "destination" => "content/search",
                        "path" => "/my/global/alias" . $index,
                        "languageCodes" => array( "eng-GB" ),
                        "alwaysAvailable" => true,
                        "isHistory" => false,
                        "isCustom" => true,
                        "forward" => true,
                    )
                ),
                $urlAlias
            );
        }
    }

    /**
     * Test for the listGlobalAliases(() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLAliasService::listGlobalAliases
     */
    public function testListGlobalAliasesWithOffsetAndLimit()
    {
        $urlAliasService = $this->repository->getURLAliasService();
        $count = 6;
        $offset = 2;
        $limit = 3;

        for ( $i = 0; $i < $count; $i++ )
        {
            $urlAliasService->createGlobalUrlAlias( "module:content/search", "my/global/alias{$i}", "eng-GB", true, true );
        }

        $urlAliases = $urlAliasService->listGlobalAliases( null, $offset, $limit );

        self::assertCount( $limit, $urlAliases );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                new UrlAlias(
                    array(
                        "id" => "42-" . md5( "alias" . ( $index + $offset ) ),
                        "type" => UrlAlias::RESOURCE,
                        "destination" => "content/search",
                        "path" => "/my/global/alias" . ( $index + $offset ),
                        "languageCodes" => array( "eng-GB" ),
                        "alwaysAvailable" => true,
                        "isHistory" => false,
                        "isCustom" => true,
                        "forward" => true,
                    )
                ),
                $urlAlias
            );
        }
    }

    /**
     * @param int $id
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Location
     */
    protected function getLocationStub( $id = 42 )
    {
        return new Location( array( "id" => $id ) );
    }

    /**
     * @param object $urlAliasService
     * @param array $configuration
     */
    protected function setConfiguration( $urlAliasService, array $configuration )
    {
        $refObject = new \ReflectionObject( $urlAliasService );
        $refProperty = $refObject->getProperty( 'settings' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $urlAliasService,
            $configuration
        );
    }
}
