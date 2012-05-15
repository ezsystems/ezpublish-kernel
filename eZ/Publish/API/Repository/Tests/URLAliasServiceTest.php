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
     * @param URLAlias $createdUrlAlias
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
     * @param URLAlias $createdUrlAlias
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
     * @param URLAlias $createdUrlAlias
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testCreateUrlAliasThrowsForbiddenException()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation( $locationId );

        // Throws ForbiddenException, since this path already exists for the
        // language
        $createdUrlAlias = $urlAliasService->createUrlAlias(
            $location, '/Support', 'eng-US'
        );
        /* END: Use Case */
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias($location, $path, $languageCode, $forwarding)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testCreateUrlAliasThrowsForbiddenExceptionWithFourthParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createUrlAlias() is not implemented." );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testCreateUrlAliasThrowsForbiddenExceptionWithFifthParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createUrlAlias() is not implemented." );
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
        $this->markTestIncomplete( "Test for URLAliasService::createGlobalUrlAlias() is not implemented." );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward)
     *
     */
    public function testCreateGlobalUrlAliasWithFourthParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createGlobalUrlAlias() is not implemented." );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward, $alwaysAvailable)
     *
     */
    public function testCreateGlobalUrlAliasWithFifthParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createGlobalUrlAlias() is not implemented." );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testCreateGlobalUrlAliasThrowsForbiddenException()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createGlobalUrlAlias() is not implemented." );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testCreateGlobalUrlAliasThrowsForbiddenExceptionWithFourthParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createGlobalUrlAlias() is not implemented." );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward, $alwaysAvailable)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testCreateGlobalUrlAliasThrowsForbiddenExceptionWithFifthParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::createGlobalUrlAlias() is not implemented." );
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
        $this->markTestIncomplete( "Test for URLAliasService::listLocationAliases() is not implemented." );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases($location, $custom)
     *
     */
    public function testListLocationAliasesWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::listLocationAliases() is not implemented." );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases($location, $custom, $languageCode)
     *
     */
    public function testListLocationAliasesWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::listLocationAliases() is not implemented." );
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
        $this->markTestIncomplete( "Test for URLAliasService::listGlobalAliases() is not implemented." );
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode)
     *
     */
    public function testListGlobalAliasesWithFirstParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::listGlobalAliases() is not implemented." );
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode, $offset)
     *
     */
    public function testListGlobalAliasesWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::listGlobalAliases() is not implemented." );
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode, $offset, $limit)
     *
     */
    public function testListGlobalAliasesWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::listGlobalAliases() is not implemented." );
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
        $this->markTestIncomplete( "Test for URLAliasService::removeAliases() is not implemented." );
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
        $this->markTestIncomplete( "Test for URLAliasService::lookUp() is not implemented." );
    }

    /**
     * Test for the lookUp() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     *
     */
    public function testLookUpWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::lookUp() is not implemented." );
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
        $this->markTestIncomplete( "Test for URLAliasService::lookUp() is not implemented." );
    }

    /**
     * Test for the lookUp() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookUpThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for URLAliasService::lookUp() is not implemented." );
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
