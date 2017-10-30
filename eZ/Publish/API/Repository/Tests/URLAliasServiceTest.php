<?php

/**
 * File containing the URLAliasServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\URLAlias;
use Exception;

/**
 * Test case for operations in the URLAliasService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\URLAliasService
 * @group url-alias
 */
class URLAliasServiceTest extends BaseTest
{
    /**
     * Tests that the required <b>LocationService::loadLocation()</b>
     * at least returns an object, because this method is utilized in several
     * tests.
     */
    protected function setUp()
    {
        parent::setUp();

        try {
            // Load the LocationService
            $locationService = $this->getRepository()->getLocationService();

            $membersUserGroupLocationId = 12;

            // Load a location instance
            $location = $locationService->loadLocation(
                $membersUserGroupLocationId
            );

            if (false === is_object($location)) {
                $this->markTestSkipped(
                    'This test cannot be executed, because the utilized ' .
                    'LocationService::loadLocation() does not ' .
                    'return an object.'
                );
            }
        } catch (Exception $e) {
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
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias()
     */
    public function testCreateUrlAlias()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation($locationId);

        $createdUrlAlias = $urlAliasService->createUrlAlias($location, '/Home/My-New-Site', 'eng-US');
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return array($createdUrlAlias, $location->id);
    }

    /**
     * @param array $testData
     *
     * @depends testCreateUrlAlias
     */
    public function testCreateUrlAliasPropertyValues(array $testData)
    {
        list($createdUrlAlias, $locationId) = $testData;

        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias($location, $path, $languageCode, $forwarding)
     * @depends testCreateUrlAliasPropertyValues
     */
    public function testCreateUrlAliasWithForwarding()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation($locationId);

        $createdUrlAlias = $urlAliasService->createUrlAlias($location, '/Home/My-New-Site', 'eng-US', true);
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return array($createdUrlAlias, $location->id);
    }

    /**
     * @param array $testData
     *
     * @depends testCreateUrlAliasWithForwarding
     */
    public function testCreateUrlAliasPropertyValuesWithForwarding(array $testData)
    {
        list($createdUrlAlias, $locationId) = $testData;

        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => true,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable)
     */
    public function testCreateUrlAliasWithAlwaysAvailable()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation($locationId);

        $createdUrlAlias = $urlAliasService->createUrlAlias($location, '/Home/My-New-Site', 'eng-US', false, true);
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return array($createdUrlAlias, $location->id);
    }

    /**
     * @param array $testData
     *
     * @depends testCreateUrlAliasWithAlwaysAvailable
     */
    public function testCreateUrlAliasPropertyValuesWithAlwaysAvailable(array $testData)
    {
        list($createdUrlAlias, $locationId) = $testData;

        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => true,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createUrlAlias()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateUrlAliasThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $location = $locationService->loadLocation($locationId);

        // Throws InvalidArgumentException, since this path already exists for the
        // language
        $createdUrlAlias = $urlAliasService->createUrlAlias($location, '/Design/Plain-site', 'eng-US');
        /* END: Use Case */
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias()
     */
    public function testCreateGlobalUrlAlias()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            'module:content/search?SearchText=eZ',
            '/Home/My-New-Site',
            'eng-US'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return $createdUrlAlias;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias
     *
     * @depends testCreateGlobalUrlAlias
     */
    public function testCreateGlobalUrlAliasPropertyValues(URLAlias $createdUrlAlias)
    {
        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::RESOURCE,
                'destination' => 'content/search?SearchText=eZ',
                'path' => '/Home/My-New-Site',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forward)
     */
    public function testCreateGlobalUrlAliasWithForward()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            'module:content/search?SearchText=eZ',
            '/Home/My-New-Site',
            'eng-US',
            true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return $createdUrlAlias;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias
     *
     * @depends testCreateGlobalUrlAliasWithForward
     */
    public function testCreateGlobalUrlAliasWithForwardPropertyValues(URLAlias $createdUrlAlias)
    {
        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::RESOURCE,
                'destination' => 'content/search?SearchText=eZ',
                'path' => '/Home/My-New-Site',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => true,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable)
     */
    public function testCreateGlobalUrlAliasWithAlwaysAvailable()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            'module:content/search?SearchText=eZ',
            '/Home/My-New-Site',
            'eng-US',
            false,
            true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return $createdUrlAlias;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias
     *
     * @depends testCreateGlobalUrlAliasWithAlwaysAvailable
     */
    public function testCreateGlobalUrlAliasWithAlwaysAvailablePropertyValues(URLAlias $createdUrlAlias)
    {
        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::RESOURCE,
                'destination' => 'content/search?SearchText=eZ',
                'path' => '/Home/My-New-Site',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => true,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable)
     */
    public function testCreateGlobalUrlAliasForLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        $locationService = $repository->getLocationService();
        $location = $locationService->loadLocation($locationId);

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            'module:content/view/full/' . $locationId,
            '/Home/My-New-Site-global',
            'eng-US',
            false,
            true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return array($createdUrlAlias, $location->id);
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable)
     */
    public function testCreateGlobalUrlAliasForLocationVariation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        $locationService = $repository->getLocationService();
        $location = $locationService->loadLocation($locationId);

        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location

        $urlAliasService = $repository->getURLAliasService();

        $createdUrlAlias = $urlAliasService->createGlobalUrlAlias(
            'eznode:' . $locationId,
            '/Home/My-New-Site-global',
            'eng-US',
            false,
            true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $createdUrlAlias
        );

        return array($createdUrlAlias, $location->id);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias
     *
     * @depends testCreateGlobalUrlAliasForLocation
     */
    public function testCreateGlobalUrlAliasForLocationPropertyValues($testData)
    {
        list($createdUrlAlias, $locationId) = $testData;

        $this->assertNotNull($createdUrlAlias->id);

        $this->assertPropertiesCorrect(
            array(
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site-global',
                'languageCodes' => array('eng-US'),
                'alwaysAvailable' => true,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ),
            $createdUrlAlias
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias
     *
     * @depends testCreateGlobalUrlAliasForLocationVariation
     */
    public function testCreateGlobalUrlAliasForLocationVariationPropertyValues($testData)
    {
        $this->testCreateGlobalUrlAliasForLocationPropertyValues($testData);
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
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
            'module:content/search?SearchText=eZ',
            '/Design/Plain-site',
            'eng-US'
        );
        /* END: Use Case */
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases()
     */
    public function testListLocationAliases()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 12);

        /* BEGIN: Use Case */
        // $locationId contains the ID of an existing Location
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);

        // Create a custom URL alias for $location
        $urlAliasService->createUrlAlias($location, '/My/Great-new-Site', 'eng-US');

        // $loadedAliases will contain an array of custom URLAlias objects
        $loadedAliases = $urlAliasService->listLocationAliases($location);
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );

        // Only 1 non-history alias
        $this->assertEquals(1, count($loadedAliases));

        return array($loadedAliases, $location);
    }

    /**
     * @param array $testData
     *
     * @depends testListLocationAliases
     */
    public function testListLocationAliasesLoadsCorrectly(array $testData)
    {
        list($loadedAliases, $location) = $testData;

        foreach ($loadedAliases as $loadedAlias) {
            $this->assertInstanceOf(
                'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
                $loadedAlias
            );
            $this->assertEquals(
                $location->id,
                $loadedAlias->destination
            );
        }
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases($location, $custom, $languageCode)
     */
    public function testListLocationAliasesWithCustomFilter()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 12);

        /* BEGIN: Use Case */
        // $locationId contains the ID of an existing Location
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);

        // Create a second URL alias for $location, this is a "custom" one
        $urlAliasService->createUrlAlias($location, '/My/Great-new-Site', 'ger-DE');

        // $loadedAliases will contain 1 aliases in eng-US only
        $loadedAliases = $urlAliasService->listLocationAliases($location, false, 'eng-US');
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals(1, count($loadedAliases));
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listLocationAliases($location, $custom)
     */
    public function testListLocationAliasesWithLanguageCodeFilter()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 12);

        /* BEGIN: Use Case */
        // $locationId contains the ID of an existing Location
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);
        // Create a custom URL alias for $location
        $urlAliasService->createUrlAlias($location, '/My/Great-new-Site', 'eng-US');

        // $loadedAliases will contain only 1 of 3 aliases (custom in eng-US)
        $loadedAliases = $urlAliasService->listLocationAliases($location, true, 'eng-US');
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals(1, count($loadedAliases));
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases()
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
        $this->assertEquals(3, count($loadedAliases));
    }

    /**
     * Creates 3 global aliases.
     */
    private function createGlobalAliases()
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();

        /* BEGIN: Inline */
        $urlAliasService->createGlobalUrlAlias(
            'module:content/search?SearchText=eZ',
            '/My/Special-Support',
            'eng-US'
        );
        $urlAliasService->createGlobalUrlAlias(
            'module:content/search?SearchText=eZ',
            '/My/London-Office',
            'eng-GB'
        );
        $urlAliasService->createGlobalUrlAlias(
            'module:content/search?SearchText=Sindelfingen',
            '/My/Fancy-Site',
            'eng-US'
        );
        /* END: Inline */
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode)
     */
    public function testListGlobalAliasesWithLanguageFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain only 2 of 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases('eng-US');
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals(2, count($loadedAliases));
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode, $offset)
     */
    public function testListGlobalAliasesWithOffset()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain only 2 of 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases(null, 1);
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals(2, count($loadedAliases));
    }

    /**
     * Test for the listGlobalAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::listGlobalAliases($languageCode, $offset, $limit)
     */
    public function testListGlobalAliasesWithLimit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create some global aliases
        $this->createGlobalAliases();

        // $loadedAliases will contain only 1 of 3 global aliases
        $loadedAliases = $urlAliasService->listGlobalAliases(null, 0, 1);
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedAliases
        );
        $this->assertEquals(1, count($loadedAliases));
    }

    /**
     * Test for the removeAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::removeAliases()
     */
    public function testRemoveAliases()
    {
        $repository = $this->getRepository();

        $locationService = $repository->getLocationService();
        $someLocation = $locationService->loadLocation(
            $this->generateId('location', 12)
        );

        /* BEGIN: Use Case */
        // $someLocation contains a location with automatically generated
        // aliases assigned
        $urlAliasService = $repository->getURLAliasService();

        $initialAliases = $urlAliasService->listLocationAliases($someLocation);

        // Creates a custom alias for $someLocation
        $urlAliasService->createUrlAlias(
            $someLocation,
            '/my/fancy/url/alias/sindelfingen',
            'eng-US'
        );

        $customAliases = $urlAliasService->listLocationAliases($someLocation);

        // The custom alias just created will be removed
        // the automatic aliases stay in tact
        $urlAliasService->removeAliases($customAliases);
        /* END: Use Case */

        $this->assertEquals(
            $initialAliases,
            $urlAliasService->listLocationAliases($someLocation)
        );
    }

    /**
     * Test for the removeAliases() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::removeAliases()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testRemoveAliasesThrowsInvalidArgumentExceptionIfAutogeneratedAliasesAreToBeRemoved()
    {
        $repository = $this->getRepository();

        $locationService = $repository->getLocationService();
        $someLocation = $locationService->loadLocation(
            $this->generateId('location', 12)
        );

        /* BEGIN: Use Case */
        // $someLocation contains a location with automatically generated
        // aliases assigned
        $urlAliasService = $repository->getURLAliasService();

        $autogeneratedAliases = $urlAliasService->listLocationAliases($someLocation, false);

        // Throws an InvalidArgumentException, since autogeneratedAliases
        // cannot be removed with this method
        $urlAliasService->removeAliases($autogeneratedAliases);
        /* END: Use Case */
    }

    /**
     * Test for the lookUp() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp()
     */
    public function testLookUp()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        $loadedAlias = $urlAliasService->lookUp('/Setup2');
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
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     */
    public function testLookUpWithLanguageFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Create aliases in multiple languages
        $this->createGlobalAliases();

        $loadedAlias = $urlAliasService->lookUp('/My/Special-Support', 'eng-US');
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias',
            $loadedAlias
        );
        $this->assertEquals(
            'content/search?SearchText=eZ',
            $loadedAlias->destination
        );
    }

    /**
     * Test for the lookUp() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookUpThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Throws NotFoundException
        $loadedAlias = $urlAliasService->lookUp('/non-existent-url');
        /* END: Use Case */
    }

    /**
     * Test for the lookUp() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookUpThrowsNotFoundExceptionWithLanguageFilter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Throws NotFoundException
        $loadedAlias = $urlAliasService->lookUp('/Contact-Us', 'ger-DE');
        /* END: Use Case */
    }

    /**
     * Test for the lookUp() method.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::lookUp($url, $languageCode)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testLookUpThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();

        // Throws InvalidArgumentException
        $loadedAlias = $urlAliasService->lookUp(str_repeat('/1', 99), 'ger-DE');
        /* END: Use Case */
    }

    /**
     * Test for the lookUp() method after renaming parent which is a part of the lookup path.
     *
     * @see https://jira.ez.no/browse/EZP-28046
     * @covers \eZ\Publish\API\Repository\URLAliasService::lookUp
     * @covers \eZ\Publish\API\Repository\URLAliasService::listLocationAliases
     */
    public function testLookupOnRenamedParent()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $locationService = $this->getRepository()->getLocationService();
        $contentTypeService = $this->getRepository()->getContentTypeService();
        $contentService = $this->getRepository()->getContentService();

        // 1. Create new container object (e.g. Folder "My Folder").
        $folderContentType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $folderCreateStruct = $contentService->newContentCreateStruct($folderContentType, 'eng-GB');
        $folderCreateStruct->setField('name', 'My-Folder');

        $folderDraft = $contentService->createContent($folderCreateStruct, [
            $locationService->newLocationCreateStruct(2),
        ]);

        $folder = $contentService->publishVersion($folderDraft->versionInfo);

        // 2. Create new object inside this container (e.g. article "My Article").
        $folderLocation = $locationService->loadLocation($folder->contentInfo->mainLocationId);

        $articleContentType = $contentTypeService->loadContentTypeByIdentifier('article');
        $articleCreateStruct = $contentService->newContentCreateStruct($articleContentType, 'eng-GB');
        $articleCreateStruct->setField('title', 'My Article');
        $articleCreateStruct->setField(
            'intro',
            <<< DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" version="5.0-variant ezpublish-1.0">
    <para>Cache invalidation in eZ</para>
</section>
DOCBOOK
        );
        $article = $contentService->publishVersion(
            $contentService->createContent($articleCreateStruct, [
                $locationService->newLocationCreateStruct($folderLocation->id),
            ])->versionInfo
        );
        $articleLocation = $locationService->loadLocation($article->contentInfo->mainLocationId);

        // 3. Navigate to both of them
        $urlAliasService->lookup('/My-Folder');
        $urlAliasService->listLocationAliases($folderLocation, false);
        $urlAliasService->lookup('/My-Folder/My-Article');
        $urlAliasService->listLocationAliases($articleLocation, false);

        // 4. Rename "My Folder" to "My Folder Modified".
        $folderDraft = $contentService->createContentDraft($folder->contentInfo);
        $folderUpdateStruct = $contentService->newContentUpdateStruct();
        $folderUpdateStruct->setField('name', 'My Folder Modified');

        $contentService->publishVersion(
            $contentService->updateContent($folderDraft->versionInfo, $folderUpdateStruct)->versionInfo
        );

        // 5. Navigate to "Article"
        $urlAliasService->lookup('/My-Folder/My-Article');
        $aliases = $urlAliasService->listLocationAliases($articleLocation, false);

        $this->assertEquals('/My-Folder-Modified/My-Article', $aliases[0]->path);
    }
}
