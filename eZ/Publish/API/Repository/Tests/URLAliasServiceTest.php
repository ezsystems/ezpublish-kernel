<?php

/**
 * File containing the URLAliasServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Tests\Common\SlugConverter as TestSlugConverter;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use Exception;
use PDO;
use RuntimeException;

/**
 * Test case for operations in the URLAliasService using in memory storage.
 *
 * @see \eZ\Publish\API\Repository\URLAliasService
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
            URLAlias::class,
            $createdUrlAlias
        );

        return [$createdUrlAlias, $location->id];
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
            [
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ],
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
            URLAlias::class,
            $createdUrlAlias
        );

        return [$createdUrlAlias, $location->id];
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
            [
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => true,
            ],
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
            URLAlias::class,
            $createdUrlAlias
        );

        return [$createdUrlAlias, $location->id];
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
            [
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => true,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ],
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
            URLAlias::class,
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
            [
                'type' => URLAlias::RESOURCE,
                'destination' => 'content/search?SearchText=eZ',
                'path' => '/Home/My-New-Site',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ],
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
            URLAlias::class,
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
            [
                'type' => URLAlias::RESOURCE,
                'destination' => 'content/search?SearchText=eZ',
                'path' => '/Home/My-New-Site',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => false,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => true,
            ],
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
            URLAlias::class,
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
            [
                'type' => URLAlias::RESOURCE,
                'destination' => 'content/search?SearchText=eZ',
                'path' => '/Home/My-New-Site',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => true,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ],
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
            URLAlias::class,
            $createdUrlAlias
        );

        return [$createdUrlAlias, $location->id];
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
            URLAlias::class,
            $createdUrlAlias
        );

        return [$createdUrlAlias, $location->id];
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
            [
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => '/Home/My-New-Site-global',
                'languageCodes' => ['eng-US'],
                'alwaysAvailable' => true,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ],
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

        $this->assertIsArray($loadedAliases);

        // Only 1 non-history alias
        $this->assertCount(1, $loadedAliases);

        return [$loadedAliases, $location];
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
                URLAlias::class,
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

        $this->assertIsArray($loadedAliases);
        $this->assertCount(1, $loadedAliases);
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

        $this->assertIsArray($loadedAliases);
        $this->assertCount(1, $loadedAliases);
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

        $this->assertIsArray($loadedAliases);
        $this->assertCount(3, $loadedAliases);
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

        $this->assertIsArray($loadedAliases);
        $this->assertCount(2, $loadedAliases);
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

        $this->assertIsArray($loadedAliases);
        $this->assertCount(2, $loadedAliases);
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

        $this->assertIsArray($loadedAliases);
        $this->assertCount(1, $loadedAliases);
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

        $loadedAlias = $urlAliasService->lookup('/Setup2');
        /* END: Use Case */

        $this->assertInstanceOf(
            URLAlias::class,
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

        $loadedAlias = $urlAliasService->lookup('/My/Special-Support', 'eng-US');
        /* END: Use Case */

        $this->assertInstanceOf(
            URLAlias::class,
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
        $urlAliasService->lookup('/non-existent-url');
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
        $urlAliasService->lookup('/Contact-Us', 'ger-DE');
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
        $loadedAlias = $urlAliasService->lookup(str_repeat('/1', 99), 'ger-DE');
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

    /**
     * Test lookup on multilingual nested Locations returns proper UrlAlias Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLookupOnMultilingualNestedLocations()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $locationService = $this->getRepository()->getLocationService();

        $topFolderNames = [
            'eng-GB' => 'My folder Name',
            'ger-DE' => 'Ger folder Name',
            'eng-US' => 'My folder Name',
        ];
        $nestedFolderNames = [
            'eng-GB' => 'nested Folder name',
            'ger-DE' => 'Ger Nested folder Name',
            'eng-US' => 'nested Folder name',
        ];
        $topFolderLocation = $locationService->loadLocation(
            $this->createFolder($topFolderNames, 2)->contentInfo->mainLocationId
        );
        $nestedFolderLocation = $locationService->loadLocation(
            $this->createFolder(
                $nestedFolderNames,
                $topFolderLocation->id
            )->contentInfo->mainLocationId
        );
        $urlAlias = $urlAliasService->lookup('/My-Folder-Name/Nested-Folder-Name');
        self::assertPropertiesCorrect(
            [
                'destination' => $nestedFolderLocation->id,
                'path' => '/My-folder-Name/nested-Folder-name',
                'languageCodes' => ['eng-US', 'eng-GB'],
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            ],
            $urlAlias
        );
        $urlAlias = $urlAliasService->lookup('/Ger-Folder-Name/Ger-Nested-Folder-Name');
        self::assertPropertiesCorrect(
            [
                'destination' => $nestedFolderLocation->id,
                'path' => '/Ger-folder-Name/Ger-Nested-folder-Name',
                'languageCodes' => ['ger-DE'],
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            ],
            $urlAlias
        );

        return [$topFolderLocation, $nestedFolderLocation];
    }

    /**
     * Test refreshSystemUrlAliasesForLocation historizes and changes current URL alias after
     * changing SlugConverter configuration.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \ErrorException
     */
    public function testRefreshSystemUrlAliasesForLocationWithChangedSlugConverterConfiguration()
    {
        list($topFolderLocation, $nestedFolderLocation) = $this->testLookupOnMultilingualNestedLocations();

        $urlAliasService = $this->getRepository(false)->getURLAliasService();

        $this->changeSlugConverterConfiguration('transformation', 'urlalias_compat');
        $this->changeSlugConverterConfiguration('wordSeparatorName', 'underscore');

        try {
            $urlAliasService->refreshSystemUrlAliasesForLocation($topFolderLocation);
            $urlAliasService->refreshSystemUrlAliasesForLocation($nestedFolderLocation);

            $urlAlias = $urlAliasService->lookup('/My-Folder-Name/Nested-Folder-Name');
            $this->assertUrlAliasPropertiesCorrect(
                $nestedFolderLocation,
                '/My-folder-Name/nested-Folder-name',
                ['eng-US', 'eng-GB'],
                true,
                $urlAlias
            );

            $urlAlias = $urlAliasService->lookup('/my_folder_name/nested_folder_name');
            $this->assertUrlAliasPropertiesCorrect(
                $nestedFolderLocation,
                '/my_folder_name/nested_folder_name',
                ['eng-US', 'eng-GB'],
                false,
                $urlAlias
            );

            $urlAlias = $urlAliasService->lookup('/Ger-Folder-Name/Ger-Nested-Folder-Name');
            $this->assertUrlAliasPropertiesCorrect(
                $nestedFolderLocation,
                '/Ger-folder-Name/Ger-Nested-folder-Name',
                ['ger-DE'],
                true,
                $urlAlias
            );

            $urlAlias = $urlAliasService->lookup('/ger_folder_name/ger_nested_folder_name');
            $this->assertUrlAliasPropertiesCorrect(
                $nestedFolderLocation,
                '/ger_folder_name/ger_nested_folder_name',
                ['ger-DE'],
                false,
                $urlAlias
            );
        } finally {
            // restore configuration
            $this->changeSlugConverterConfiguration('transformation', 'urlalias');
            $this->changeSlugConverterConfiguration('wordSeparatorName', 'dash');
        }
    }

    /**
     * Test that URL aliases are refreshed after changing URL alias schema Field name of a Content Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRefreshSystemUrlAliasesForContentsWithUpdatedContentTypes()
    {
        list($topFolderLocation, $nestedFolderLocation) = $this->testLookupOnMultilingualNestedLocations();
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $topFolderLocation */
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $nestedFolderLocation */

        // Default URL Alias schema is <short_name|name> which messes up this test, so:
        $this->changeContentTypeUrlAliasSchema('folder', '<name>');

        $urlAliasService = $this->getRepository(false)->getURLAliasService();

        $this->updateContentField(
            $topFolderLocation->getContentInfo(),
            'short_name',
            ['eng-GB' => 'EN Short Name', 'ger-DE' => 'DE Short Name']
        );
        $this->updateContentField(
            $nestedFolderLocation->getContentInfo(),
            'short_name',
            ['eng-GB' => 'EN Nested Short Name', 'ger-DE' => 'DE Nested Short Name']
        );

        $this->changeContentTypeUrlAliasSchema('folder', '<short_name>');

        // sanity test, done after updating CT, because it does not update existing entries by design
        $this->assertUrlIsCurrent('/My-folder-Name', $topFolderLocation->id);
        $this->assertUrlIsCurrent('/Ger-folder-Name', $topFolderLocation->id);
        $this->assertUrlIsCurrent('/My-folder-Name/nested-Folder-name', $nestedFolderLocation->id);
        $this->assertUrlIsCurrent('/Ger-folder-Name/Ger-Nested-folder-Name', $nestedFolderLocation->id);

        // Call API being tested
        $urlAliasService->refreshSystemUrlAliasesForLocation($topFolderLocation);
        $urlAliasService->refreshSystemUrlAliasesForLocation($nestedFolderLocation);

        // check archived aliases
        $this->assertUrlIsHistory('/My-folder-Name', $topFolderLocation->id);
        $this->assertUrlIsHistory('/Ger-folder-Name', $topFolderLocation->id);
        $this->assertUrlIsHistory('/My-folder-Name/nested-Folder-name', $nestedFolderLocation->id);
        $this->assertUrlIsHistory('/Ger-folder-Name/Ger-Nested-folder-Name', $nestedFolderLocation->id);

        // check new current aliases
        $this->assertUrlIsCurrent('/EN-Short-Name', $topFolderLocation->id);
        $this->assertUrlIsCurrent('/DE-Short-Name', $topFolderLocation->id);
        $this->assertUrlIsCurrent('/EN-Short-Name/EN-Nested-Short-Name', $nestedFolderLocation->id);
        $this->assertUrlIsCurrent('/DE-Short-Name/DE-Nested-Short-Name', $nestedFolderLocation->id);
    }

    /**
     * Test that created non-latin aliases are non-empty and unique.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateNonLatinNonEmptyUniqueAliases()
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $folderNames = [
            'eng-GB' => 'ひらがな',
        ];

        $folderLocation1 = $locationService->loadLocation(
            $this->createFolder($folderNames, 2)->contentInfo->mainLocationId
        );
        $urlAlias1 = $urlAliasService->lookup('/1');
        self::assertPropertiesCorrect(
            [
                'destination' => $folderLocation1->id,
                'path' => '/1',
                'languageCodes' => ['eng-GB'],
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            ],
            $urlAlias1
        );

        $folderLocation2 = $locationService->loadLocation(
            $this->createFolder($folderNames, 2)->contentInfo->mainLocationId
        );
        $urlAlias2 = $urlAliasService->lookup('/2');
        self::assertPropertiesCorrect(
            [
                'destination' => $folderLocation2->id,
                'path' => '/2',
                'languageCodes' => ['eng-GB'],
                'isHistory' => false,
                'isCustom' => false,
                'forward' => false,
            ],
            $urlAlias2
        );
    }

    /**
     * Test restoring missing current URL which has existing history.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \Exception
     */
    public function testRefreshSystemUrlAliasesForMissingUrlWithHistory()
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $folderNames = ['eng-GB' => 'My folder Name'];
        $folder = $this->createFolder($folderNames, 2);
        $folderLocation = $locationService->loadLocation($folder->contentInfo->mainLocationId);
        $nestedFolder = $this->createFolder(['eng-GB' => 'Nested folder'], $folderLocation->id);
        $nestedFolderLocation = $locationService->loadLocation($nestedFolder->contentInfo->mainLocationId);

        $folder = $this->updateContentField(
            $folder->contentInfo,
            'name',
            ['eng-GB' => 'Updated Name']
        );
        // create more historical entries
        $this->updateContentField(
            $folder->contentInfo,
            'name',
            ['eng-GB' => 'Updated Again Name']
        );
        // create historical entry for nested folder
        $this->updateContentField(
            $nestedFolder->contentInfo,
            'name',
            ['eng-GB' => 'Updated Nested folder']
        );

        // perform sanity check
        $this->assertUrlIsHistory('/My-folder-Name', $folderLocation->id);
        $this->assertUrlIsHistory('/Updated-Name', $folderLocation->id);
        $this->assertUrlIsHistory('/My-folder-Name/Nested-folder', $nestedFolderLocation->id);
        $this->assertUrlIsHistory('/Updated-Name/Nested-folder', $nestedFolderLocation->id);
        $this->assertUrlIsHistory('/Updated-Again-Name/Nested-folder', $nestedFolderLocation->id);

        $this->assertUrlIsCurrent('/Updated-Again-Name', $folderLocation->id);
        $this->assertUrlIsCurrent('/Updated-Again-Name/Updated-Nested-folder', $nestedFolderLocation->id);

        self::assertNotEmpty($urlAliasService->listLocationAliases($folderLocation, false));

        // corrupt database by removing original entry, keeping its history
        $this->performRawDatabaseOperation(
            function (Connection $connection) use ($folderLocation) {
                $queryBuilder = $connection->createQueryBuilder();
                $expr = $queryBuilder->expr();
                $queryBuilder
                    ->delete('ezurlalias_ml')
                    ->where(
                        $expr->andX(
                            $expr->eq(
                                'action',
                                $queryBuilder->createPositionalParameter(
                                    "eznode:{$folderLocation->id}"
                                )
                            ),
                            $expr->eq(
                                'is_original',
                                $queryBuilder->createPositionalParameter(1)
                            )
                        )
                    );

                return $queryBuilder->execute();
            }
        );

        // perform sanity check
        self::assertEmpty($urlAliasService->listLocationAliases($folderLocation, false));

        // Begin the actual test
        $urlAliasService->refreshSystemUrlAliasesForLocation($folderLocation);
        $urlAliasService->refreshSystemUrlAliasesForLocation($nestedFolderLocation);

        // make sure there is no corrupted data that could affect the test
        $urlAliasService->deleteCorruptedUrlAliases();

        // test if history was restored
        $this->assertUrlIsHistory('/My-folder-Name', $folderLocation->id);
        $this->assertUrlIsHistory('/Updated-Name', $folderLocation->id);
        $this->assertUrlIsHistory('/My-folder-Name/Nested-folder', $nestedFolderLocation->id);
        $this->assertUrlIsHistory('/Updated-Name/Nested-folder', $nestedFolderLocation->id);
        $this->assertUrlIsHistory('/Updated-Again-Name/Nested-folder', $nestedFolderLocation->id);

        $this->assertUrlIsCurrent('/Updated-Again-Name', $folderLocation->id);
        $this->assertUrlIsCurrent('/Updated-Again-Name/Updated-Nested-folder', $nestedFolderLocation->id);
    }

    /**
     * Test edge case when updated and archived entry gets moved to another subtree.
     *
     * @see https://jira.ez.no/browse/EZP-30004
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \Exception
     */
    public function testRefreshSystemUrlAliasesForMovedLocation()
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $folderNames = ['eng-GB' => 'folder'];
        $folder = $this->createFolder($folderNames, 2);
        $nestedFolder = $this->createFolder($folderNames, $folder->contentInfo->mainLocationId);

        $nestedFolder = $this->updateContentField(
            $nestedFolder->contentInfo,
            'name',
            ['eng-GB' => 'folder2']
        );

        $nestedFolderLocation = $locationService->loadLocation(
            $nestedFolder->contentInfo->mainLocationId
        );
        $rootLocation = $locationService->loadLocation(2);

        $locationService->moveSubtree($nestedFolderLocation, $rootLocation);
        // reload nested Location to get proper parent information
        $nestedFolderLocation = $locationService->loadLocation($nestedFolderLocation->id);

        // corrupt database by breaking link to the original URL alias
        $this->performRawDatabaseOperation(
            function (Connection $connection) use ($nestedFolderLocation) {
                $queryBuilder = $connection->createQueryBuilder();
                $expr = $queryBuilder->expr();
                $queryBuilder
                    ->update('ezurlalias_ml')
                    ->set('link', $queryBuilder->createPositionalParameter(666, \PDO::PARAM_INT))
                    ->where(
                        $expr->eq(
                            'action',
                            $queryBuilder->createPositionalParameter(
                                "eznode:{$nestedFolderLocation->id}"
                            )
                        )
                    )
                    ->andWhere(
                        $expr->eq(
                            'is_original',
                            $queryBuilder->createPositionalParameter(0, \PDO::PARAM_INT)
                        )
                    )
                    ->andWhere(
                        $expr->eq('text', $queryBuilder->createPositionalParameter('folder'))
                    )
                ;

                return $queryBuilder->execute();
            }
        );

        $urlAliasService->refreshSystemUrlAliasesForLocation($nestedFolderLocation);
    }

    /**
     * Lookup given URL and check if it is archived and points to the given Location Id.
     *
     * @param string $lookupUrl
     * @param int $expectedDestination Expected Location ID
     */
    protected function assertUrlIsHistory($lookupUrl, $expectedDestination)
    {
        $this->assertLookupHistory(true, $expectedDestination, $lookupUrl);
    }

    /**
     * Lookup given URL and check if it is current (not archived) and points to the given Location Id.
     *
     * @param string $lookupUrl
     * @param int $expectedDestination Expected Location ID
     */
    protected function assertUrlIsCurrent($lookupUrl, $expectedDestination)
    {
        $this->assertLookupHistory(false, $expectedDestination, $lookupUrl);
    }

    /**
     * Lookup and URLAlias VO history and destination properties.
     *
     * @see assertUrlIsHistory
     * @see assertUrlIsCurrent
     *
     * @param bool $expectedIsHistory
     * @param int $expectedDestination Expected Location ID
     * @param string $lookupUrl
     */
    protected function assertLookupHistory($expectedIsHistory, $expectedDestination, $lookupUrl)
    {
        $urlAliasService = $this->getRepository(false)->getURLAliasService();

        try {
            $urlAlias = $urlAliasService->lookup($lookupUrl);
            self::assertPropertiesCorrect(
                [
                    'destination' => $expectedDestination,
                    'path' => $lookupUrl,
                    'isHistory' => $expectedIsHistory,
                ],
                $urlAlias
            );
        } catch (InvalidArgumentException $e) {
            self::fail("Failed to lookup {$lookupUrl}: $e");
        } catch (NotFoundException $e) {
            self::fail("Failed to lookup {$lookupUrl}: $e");
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param $fieldDefinitionIdentifier
     * @param array $fieldValues
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function updateContentField(ContentInfo $contentInfo, $fieldDefinitionIdentifier, array $fieldValues)
    {
        $contentService = $this->getRepository(false)->getContentService();

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        foreach ($fieldValues as $languageCode => $fieldValue) {
            $contentUpdateStruct->setField($fieldDefinitionIdentifier, $fieldValue, $languageCode);
        }
        $contentDraft = $contentService->updateContent(
            $contentService->createContentDraft($contentInfo)->versionInfo,
            $contentUpdateStruct
        );

        return $contentService->publishVersion($contentDraft->versionInfo);
    }

    /**
     * Test deleting corrupted URL aliases.
     *
     * Note: this test will not be needed once we introduce Improved Storage with Foreign keys support.
     *
     * Note: test depends on already broken URL aliases: eznode:59, eznode:59, eznode:60.
     *
     * @throws \ErrorException
     */
    public function testDeleteCorruptedUrlAliases()
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();
        $connection = $this->getRawDatabaseConnection();

        $query = $connection->createQueryBuilder()->select('*')->from('ezurlalias_ml');
        $originalRows = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $expectedCount = count($originalRows);
        $expectedCount += $this->insertBrokenUrlAliasTableFixtures($connection);

        // sanity check
        $updatedRows = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
        self::assertCount($expectedCount, $updatedRows, 'Found unexpected number of new rows');

        // BEGIN API use case
        $urlAliasService->deleteCorruptedUrlAliases();
        // END API use case

        $updatedRows = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
        self::assertCount(
            // API should also remove already broken pre-existing URL aliases to Locations 50 and 2x 59
            count($originalRows) - 3,
            $updatedRows,
            'Number of rows after cleanup is not the same as the original number of rows'
        );
    }

    /**
     * Mutate 'ezpublish.persistence.slug_converter' Service configuration.
     *
     * @param string $key
     * @param string $value
     *
     * @throws \ErrorException
     * @throws \Exception
     */
    protected function changeSlugConverterConfiguration($key, $value)
    {
        $testSlugConverter = $this
            ->getSetupFactory()
            ->getServiceContainer()
            ->getInnerContainer()
            ->get('ezpublish.persistence.slug_converter');

        if (!$testSlugConverter instanceof TestSlugConverter) {
            throw new RuntimeException(
                sprintf(
                    '%s: expected instance of %s, got %s',
                    __METHOD__,
                    TestSlugConverter::class,
                    get_class($testSlugConverter)
                )
            );
        }

        $testSlugConverter->setConfigurationValue($key, $value);
    }

    /**
     * Update Content Type URL alias schema pattern.
     *
     * @param string $contentTypeIdentifier
     * @param string $newUrlAliasSchema
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function changeContentTypeUrlAliasSchema($contentTypeIdentifier, $newUrlAliasSchema)
    {
        $contentTypeService = $this->getRepository(false)->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);

        $contentTypeDraft = $contentTypeService->createContentTypeDraft($contentType);
        $contentTypeUpdateStruct = $contentTypeService->newContentTypeUpdateStruct();
        $contentTypeUpdateStruct->urlAliasSchema = $newUrlAliasSchema;

        $contentTypeService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }

    private function assertUrlAliasPropertiesCorrect(
        Location $expectedDestinationLocation,
        $expectedPath,
        array $expectedLanguageCodes,
        $expectedIsHistory,
        URLAlias $actualUrlAliasValue
    ) {
        self::assertPropertiesCorrect(
            [
                'destination' => $expectedDestinationLocation->id,
                'path' => $expectedPath,
                // @todo uncomment after fixing EZP-27124
                //'languageCodes' => $expectedLanguageCodes,
                'isHistory' => $expectedIsHistory,
                'isCustom' => false,
                'forward' => false,
            ],
            $actualUrlAliasValue
        );
    }

    /**
     * Insert intentionally broken rows into ezurlalias_ml table to test cleanup API.
     *
     * @see \eZ\Publish\API\Repository\URLAliasService::deleteCorruptedUrlAliases
     * @see testDeleteCorruptedUrlAliases
     *
     * @param \Doctrine\DBAL\Connection $connection
     *
     * @return int Number of new rows
     */
    private function insertBrokenUrlAliasTableFixtures(Connection $connection)
    {
        $rows = [
            // link to non-existent location
            [
                'action' => 'eznode:9999',
                'action_type' => 'eznode',
                'alias_redirects' => 0,
                'id' => 9997,
                'is_alias' => 0,
                'is_original' => 1,
                'lang_mask' => 3,
                'link' => 9997,
                'parent' => 0,
                'text' => 'my-location',
                'text_md5' => '19d12b1b9994619cd8e90f00a6f5834e',
            ],
            // link to non-existent target URL alias (`link` column)
            [
                'action' => 'nop:',
                'action_type' => 'nop',
                'alias_redirects' => 0,
                'id' => 9998,
                'is_alias' => 1,
                'is_original' => 1,
                'lang_mask' => 2,
                'link' => 9995,
                'parent' => 0,
                'text' => 'my-alias1',
                'text_md5' => 'a29dd95ccf4c1bc7ebbd61086863b632',
            ],
            // link to non-existent parent URL alias
            [
                'action' => 'nop:',
                'action_type' => 'nop',
                'alias_redirects' => 0,
                'id' => 9999,
                'is_alias' => 0,
                'is_original' => 1,
                'lang_mask' => 3,
                'link' => 9999,
                'parent' => 9995,
                'text' => 'my-alias2',
                'text_md5' => 'e5dea18481e4f86857865d9fc94e4ce9',
            ],
        ];

        $query = $connection->createQueryBuilder()->insert('ezurlalias_ml');

        foreach ($rows as $row) {
            foreach ($row as $columnName => $value) {
                $row[$columnName] = $query->createNamedParameter($value);
            }
            $query->values($row);
            $query->execute();
        }

        return count($rows);
    }
}
