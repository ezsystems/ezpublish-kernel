<?php
/**
 * File containing the URLAliasServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\URLAlias;

/**
 * Test case for operations in the URLAliasService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\URLAliasService
 */
class URLAliasServiceTest extends \eZ\Publish\API\Repository\Tests\BaseTest
{
    /**
     * Tests that the required <b>LocationService::loadLocation()</b>
     * at least returns an object, because this method is utilized in several
     * tests.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        try
        {
            // Load the LocationService
            $locationService = $this->getRepository()->getLocationService();

            $membersUserGroupLocationId = 12;

            // Load a location instance
            $location = $locationService->loadLocation(
                $membersUserGroupLocationId
            );

            if ( false === is_object( $location ) )
            {
                $this->markTestSkipped(
                    'This test cannot be executed, because the utilized ' .
                    'LocationService::loadLocation() does not ' .
                    'return an object.'
                );
            }
        }
        catch ( \Exception $e )
        {
            $this->markTestSkipped(
                'This test cannot be executed, because the utilized ' .
                'LocationService::loadLocation() failed with ' .
                PHP_EOL . PHP_EOL .
                $e->getTraceAsString()
            );
        }

    }
    /**
     * Test for the createUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias()
     *
     */
    public function testCreateUrlAlias()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation( $locationId );

        $createdUrlAlias = $urlAliasService->createUrlAlias(
            $location, '/Home/My-New-Site', 'eng-US'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );
        return array( $createdUrlAlias, $location );
    }

    /**
     * @param array $testData
     * @return void
     * @depends testCreateUrlAlias
     */
    public function testCreateUrlAliasPropertyValues( array $testData )
    {
        list( $createdUrlAlias, $location ) = $testData;

        $this->assertNotNull( $createdUrlAlias->id );

        $this->assertPropertiesCorrect(
            array(
                'type'            => URLAlias::LOCATION,
                'destination'     => $location,
                'path'            => '/Home/My-New-Site',
                'languageCodes'   => array( 'eng-US' ),
                'alwaysAvailable' => false,
                'isHistory'       => false,
                'forward'         => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias($location, $path, $languageCode, $forwarding)
     * @depends testCreateUrlAliasPropertyValues
     */
    public function testCreateUrlAliasWithForwarding()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation( $locationId );

        $createdUrlAlias = $urlAliasService->createUrlAlias(
            $location, '/Home/My-New-Site', 'eng-US', true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );
        return array( $createdUrlAlias, $location );
    }

    /**
     * @param array $testData
     * @return void
     * @depends testCreateUrlAliasWithForwarding
     */
    public function testCreateUrlAliasPropertyValuesWithForwarding( array $testData )
    {
        list( $createdUrlAlias, $location ) = $testData;

        $this->assertNotNull( $createdUrlAlias->id );

        $this->assertPropertiesCorrect(
            array(
                'type'            => URLAlias::LOCATION,
                'destination'     => $location,
                'path'            => '/Home/My-New-Site',
                'languageCodes'   => array( 'eng-US' ),
                'alwaysAvailable' => false,
                'isHistory'       => false,
                'forward'         => true,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable)
     *
     */
    public function testCreateUrlAliasWithAlwaysAvailable()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation( $locationId );

        $createdUrlAlias = $urlAliasService->createUrlAlias(
            $location, '/Home/My-New-Site', 'eng-US', false, true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );
        return array( $createdUrlAlias, $location );
    }

    /**
     * @param array $testData
     * @return void
     * @depends testCreateUrlAliasWithAlwaysAvailable
     */
    public function testCreateUrlAliasPropertyValuesWithAlwaysAvailable( array $testData )
    {
        list( $createdUrlAlias, $location ) = $testData;

        $this->assertNotNull( $createdUrlAlias->id );

        $this->assertPropertiesCorrect(
            array(
                'type'            => URLAlias::LOCATION,
                'destination'     => $location,
                'path'            => '/Home/My-New-Site',
                'languageCodes'   => array( 'eng-US' ),
                'alwaysAvailable' => true,
                'isHistory'       => false,
                'forward'         => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateUrlAliasThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation( $locationId );

        // Throws InvalidArgumentException, since this path already exists for the
        // language
        $createdUrlAlias = $urlAliasService->createUrlAlias(
            $location, '/Support', 'eng-US'
        );
        /* END: Use Case */
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias()
     *
     */
    public function testCreateGlobalUrlAlias()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            '/Home/My-Site', '/Home/My-New-Site', 'eng-US'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );
        return $createdUrlAlias;
    }

    /**
     * @param eZ\Publish\API\Repository\Values\Content\URLAlias
     * @return void
     * @depends testCreateGlobalUrlAlias
     */
    public function testCreateGlobalUrlAliasPropertyValues( URLAlias $createdUrlAlias )
    {
        $this->assertNotNull( $createdUrlAlias->id );

        $this->assertPropertiesCorrect(
            array(
                'type'            => URLAlias::RESOURCE,
                'destination'     => '/Home/My-Site',
                'path'            => '/Home/My-New-Site',
                'languageCodes'   => array( 'eng-US' ),
                'alwaysAvailable' => false,
                'isHistory'       => false,
                'forward'         => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward)
     *
     */
    public function testCreateGlobalUrlAliasWithForward()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            '/Home/My-Site', '/Home/My-New-Site', 'eng-US', true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );
        return $createdUrlAlias;
    }

    /**
     * @param eZ\Publish\API\Repository\Values\Content\URLAlias
     * @return void
     * @depends testCreateGlobalUrlAliasWithForward
     */
    public function testCreateGlobalUrlAliasWithForwardPropertyValues( URLAlias $createdUrlAlias )
    {
        $this->assertNotNull( $createdUrlAlias->id );

        $this->assertPropertiesCorrect(
            array(
                'type'            => URLAlias::RESOURCE,
                'destination'     => '/Home/My-Site',
                'path'            => '/Home/My-New-Site',
                'languageCodes'   => array( 'eng-US' ),
                'alwaysAvailable' => false,
                'isHistory'       => false,
                'forward'         => true,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward, $alwaysAvailable)
     *
     */
    public function testCreateGlobalUrlAliasWithAlwaysAvailable()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            '/Home/My-Site', '/Home/My-New-Site', 'eng-US', false, true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );
        return $createdUrlAlias;
    }

    /**
     * @param eZ\Publish\API\Repository\Values\Content\URLAlias
     * @return void
     * @depends testCreateGlobalUrlAliasWithAlwaysAvailable
     */
    public function testCreateGlobalUrlAliasWithAlwaysAvailablePropertyValues( URLAlias $createdUrlAlias )
    {
        $this->assertNotNull( $createdUrlAlias->id );

        $this->assertPropertiesCorrect(
            array(
                'type'            => URLAlias::RESOURCE,
                'destination'     => '/Home/My-Site',
                'path'            => '/Home/My-New-Site',
                'languageCodes'   => array( 'eng-US' ),
                'alwaysAvailable' => true,
                'isHistory'       => false,
                'forward'         => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateGlobalUrlAliasThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Throws InvalidArgumentException, since this path already exists for the
        // language
        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            '/My-new-Site', '/Support', 'eng-US'
        );
        /* END: Use Case */
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases()
     *
     */
    public function testListLocationAliases()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 12 );

        /* BEGIN: Use Case */
        // $locationId contains the ID of an existing Location
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $locationId );

        // $loadedAliases will contain an array of URLAlias objects
        $loadedAliases = $urlAliasService->listLocationAliases( $location );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        return array( $loadedAliases, $location );
    }

    /**
     * @param array $testData
     * @return void
     * @depends testListLocationAliases
     */
    public function testListLocationAliasesLoadsCorrectly( array $testData )
    {
        list( $loadedAliases, $location ) = $testData;

        // FIXME: This is only the number of non-history aliases
        // What about those? How to load their data properly?
        $this->assertEquals( 1, count( $loadedAliases ) );

        foreach ( $loadedAliases as $loadedAlias )
        {
            $this->assertInstanceOf(
                'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
                $loadedAlias
            );
            $this->assertInstanceOf(
                'eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $loadedAlias->destination
            );
            $this->assertEquals(
                $location->id,
                $loadedAlias->destination->id
            );
        }
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases($location, $custom, $languageCode)
     *
     */
    public function testListLocationAliasesWithCustomFilter()
    {
        $this->markTestIncomplete( "Needs discussion with CBA." );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases($location, $custom)
     *
     */
    public function testListLocationAliasesWithLanguageCodeFilter()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 12 );

        /* BEGIN: Use Case */
        // $locationId contains the ID of an existing Location
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $locationId );
        // Create a second URL alias for $location
        $urlAliasService->createUrlAlias(
            $location, '/My/Great-new-Site', 'nor-NO'
        );

        // $loadedAliases will contain only 1 of 2 aliases
        $loadedAliases = $urlAliasService->listLocationAliases(
            $location, true, 'eng-US'
        );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals( 1, count( $loadedAliases ) );
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases()
     *
     */
    public function testListGlobalAliases()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain all 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases();
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals( 3, count( $loadedAliases ) );
    }

    /**
     * Creates 3 global aliases.
     *
     * @return void
     */
    private function createGlobalAliases()
    {
        $repository      = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();

        /* BEGIN: Inline */
        $urlAliasService->createGlobalUrlAlias(
            '/Support', '/My/Special-Support', 'eng-US'
        );
        $urlAliasService->createGlobalUrlAlias(
            '/Support', '/My/Spezial-Unterstützung', 'ger-DE'
        );
        $urlAliasService->createGlobalUrlAlias(
            '/Home', '/My/Fancy-Site', 'eng-US'
        );
        /* END: Inline */
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode)
     *
     */
    public function testListGlobalAliasesWithLanguageFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain only 2 of 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases( 'eng-US' );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals( 2, count( $loadedAliases ) );
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode, $offset)
     *
     */
    public function testListGlobalAliasesWithOffset()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain only 2 of 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases( null, 1 );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals( 2, count( $loadedAliases ) );
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode, $offset, $limit)
     *
     */
    public function testListGlobalAliasesWithLimit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain only 1 of 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases( null, 0, 1 );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals( 1, count( $loadedAliases ) );
    }

    /**
     * Test for the removeAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::removeAliases()
     *
     */
    public function testRemoveAliases()
    {
        $repository = $this->getRepository();

        $locationService = $repository->getLocationService();
        $firstLocation = $locationService->loadLocation(
            $this->generateId( 'location', 12 )
        );
        $secondLocation = $locationService->loadLocation(
            $this->generateId( 'location', 58 )
        );

        /* BEGIN: Use Case */
        // $firstLocation and $secondLocation contain different Locations which
        // have both URLAliases assigned
        $urlAliasService = $repository->getURLAliasService();

        // $loadedAliases will contain an array of URLAlias objects
        $loadedAliases = array_merge(
            $urlAliasService->listLocationAliases( $firstLocation ),
            $urlAliasService->listLocationAliases( $secondLocation )
        );

        // All URLAliases in $loadedAliases will be removed
        $urlAliasService->removeAliases( $loadedAliases );
        /* END: Use Case */

        $this->assertEquals(
            array(),
            $urlAliasService->listLocationAliases( $firstLocation )
        );
        $this->assertEquals(
            array(),
            $urlAliasService->listLocationAliases( $secondLocation )
        );
    }

    /**
     * Test for the lookUp() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp()
     *
     */
    public function testLookUp()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $loadedAlias = $urlAliasService->lookUp( '/Setup2' );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $loadedAlias
        );
        return $loadedAlias;
    }

    /**
     * Test for the lookUp() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     *
     */
    public function testLookUpWithLanguageFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create aliases in multiple languages
        $this->createGlobalAliases();

        $loadedAlias = $urlAliasService->lookUp( '/My/Special-Support', 'eng-US' );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $loadedAlias
        );
        $this->assertEquals(
            '/Support',
            $loadedAlias->destination
        );
    }

    /**
     * Test for the lookUp() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookUpThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Throws NotFoundException
        $loadedAlias = $urlAliasService->lookUp( '/non-existent-url' );
        /* END: Use Case */
    }

    /**
     * Test for the lookUp() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookUpThrowsNotFoundExceptionWithLanguageFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Throws NotFoundException
        $loadedAlias = $urlAliasService->lookUp( '/Setup2', 'ger-DE' );
        /* END: Use Case */
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::reverseLookup()
     *
     */
    public function testReverseLookup()
    {
        $this->markTestIncomplete( "Test for URLAliasService::reverseLookup() is not implemented." );
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::reverseLookup($location, $languageCode)
     *
     */
    public function testReverseLookupWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::reverseLookup() is not implemented." );
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::reverseLookup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testReverseLookupThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for URLAliasService::reverseLookup() is not implemented." );
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::reverseLookup($location, $languageCode)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testReverseLookupThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::reverseLookup() is not implemented." );
    }

}
