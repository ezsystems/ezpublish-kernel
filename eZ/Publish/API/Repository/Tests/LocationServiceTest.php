<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use Exception;
use eZ\Publish\API\Repository\Exceptions\BadStateException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;

/**
 * Test case for operations in the LocationService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LocationService
 * @group location
 */
class LocationServiceTest extends BaseTest
{
    /**
     * Test for the newLocationCreateStruct() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     *
     * @see \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct()
     */
    public function testNewLocationCreateStruct()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId('location', 1);
        /* BEGIN: Use Case */
        // $parentLocationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $locationCreate = $locationService->newLocationCreateStruct(
            $parentLocationId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationCreateStruct',
            $locationCreate
        );

        return $locationCreate;
    }

    /**
     * Test for the newLocationCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreate
     *
     * @see \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     */
    public function testNewLocationCreateStructValues(LocationCreateStruct $locationCreate)
    {
        $this->assertPropertiesCorrect(
            [
                'priority' => 0,
                'hidden' => false,
                // remoteId should be initialized with a default value
                //'remoteId' => null,
                'sortField' => null,
                'sortOrder' => null,
                'parentLocationId' => $this->generateId('location', 1),
            ],
            $locationCreate
        );
    }

    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     */
    public function testCreateLocation()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 41);
        $parentLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);
        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = 'sindelfingen';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        $location = $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $location
        );

        return [
            'locationCreate' => $locationCreate,
            'createdLocation' => $location,
            'contentInfo' => $contentInfo,
            'parentLocation' => $locationService->loadLocation($this->generateId('location', 5)),
        ];
    }

    /**
     * Test for the createLocation() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testHideContent
     */
    public function testCreateLocationChecksContentVisibility(): void
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 41);
        $parentLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);
        $contentService->hideContent($contentInfo);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);
        $locationCreate->priority = 23;
        $locationCreate->hidden = false;
        $locationCreate->remoteId = 'sindelfingen';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        $location = $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */

        self::assertInstanceOf(Location::class, $location);

        self::assertTrue($location->invisible);
    }

    /**
     * Test for the createLocation() method with utilizing default ContentType sorting options.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocationWithContentTypeSortingOptions(): void
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 41);
        $parentLocationId = $this->generateId('location', 5);
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);

        // ContentType loading
        $contentType = $contentTypeService->loadContentType($contentInfo->contentTypeId);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);
        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = 'sindelfingen';

        $location = $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );

        $this->assertEquals($contentType->defaultSortField, $location->sortField);
        $this->assertEquals($contentType->defaultSortOrder, $location->sortOrder);
    }

    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationStructValues(array $data)
    {
        $locationCreate = $data['locationCreate'];
        $createdLocation = $data['createdLocation'];
        $contentInfo = $data['contentInfo'];

        $this->assertPropertiesCorrect(
            [
                'priority' => $locationCreate->priority,
                'hidden' => $locationCreate->hidden,
                'invisible' => $locationCreate->hidden,
                'remoteId' => $locationCreate->remoteId,
                'contentInfo' => $contentInfo,
                'parentLocationId' => $locationCreate->parentLocationId,
                'pathString' => '/1/5/' . $this->parseId('location', $createdLocation->id) . '/',
                'depth' => 2,
                'sortField' => $locationCreate->sortField,
                'sortOrder' => $locationCreate->sortOrder,
            ],
            $createdLocation
        );

        $this->assertNotNull($createdLocation->id);
    }

    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionContentAlreadyBelowParent()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 11);
        $parentLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location which already
        // has the content assigned to one of its descendant locations
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        // Throws exception, since content is already located at "/1/2/107/110/"
        $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionParentIsSubLocationOfContent()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 4);
        $parentLocationId = $this->generateId('location', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location which is below a
        // location that is assigned to the content
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        // Throws exception, since content is already located at "/1/2/"
        $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionRemoteIdExists()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 41);
        $parentLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);
        // This remote ID already exists
        $locationCreate->remoteId = 'f3e90596361e31d496d4026eb624c983';

        // Throws exception, since remote ID is already in use
        $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the createLocation() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @dataProvider dataProviderForOutOfRangeLocationPriority
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionPriorityIsOutOfRange($priority)
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 41);
        $parentLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo($contentId);

        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);
        $locationCreate->priority = $priority;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = 'sindelfingen';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Throws exception, since priority is out of range
        $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    public function dataProviderForOutOfRangeLocationPriority()
    {
        return [[-2147483649], [2147483648]];
    }

    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 41);
        $parentLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $repository->beginTransaction();

        try {
            // ContentInfo for "How to use eZ Publish"
            $contentInfo = $contentService->loadContentInfo($contentId);

            $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);
            $locationCreate->remoteId = 'sindelfingen';

            $createdLocationId = $locationService->createLocation(
                $contentInfo,
                $locationCreate
            )->id;
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $repository->rollback();

        try {
            // Throws exception since creation of location was rolled back
            $location = $locationService->loadLocation($createdLocationId);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Objects still exists after rollback.');
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocation
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testLoadLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);
        /* END: Use Case */

        $this->assertInstanceOf(
            Location::class,
            $location
        );
        self::assertEquals(5, $location->id);

        return $location;
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationRootStructValues()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $location = $locationService->loadLocation($this->generateId('location', 1));

        $this->assertRootLocationStructValues($location);
    }

    public function testLoadLocationRootStructValuesWithPrioritizedLanguages(): void
    {
        $repository = $this->getRepository();

        $rootLocation = $repository
            ->getLocationService()
            ->loadLocation(
                $this->generateId('location', 1),
                [
                    'eng-GB',
                    'ger-DE',
                ]
            );

        $this->assertRootLocationStructValues($rootLocation);
    }

    private function assertRootLocationStructValues(Location $location): void
    {
        $legacyDateTime = new \DateTime();
        $legacyDateTime->setTimestamp(1030968000);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertPropertiesCorrect(
            [
                'id' => $this->generateId('location', 1),
                'status' => 1,
                'priority' => 0,
                'hidden' => false,
                'invisible' => false,
                'remoteId' => '629709ba256fe317c3ddcee35453a96a',
                'parentLocationId' => $this->generateId('location', 1),
                'pathString' => '/1/',
                'depth' => 0,
                'sortField' => 1,
                'sortOrder' => 1,
            ],
            $location
        );

        $this->assertInstanceOf(ContentInfo::class, $location->contentInfo);
        $this->assertPropertiesCorrect(
            [
                'id' => $this->generateId('content', 0),
                'name' => 'Top Level Nodes',
                'sectionId' => 1,
                'mainLocationId' => 1,
                'contentTypeId' => 1,
                'currentVersionNo' => 1,
                'published' => 1,
                'ownerId' => 14,
                'modificationDate' => $legacyDateTime,
                'publishedDate' => $legacyDateTime,
                'alwaysAvailable' => 1,
                'remoteId' => null,
                'mainLanguageCode' => 'eng-GB',
            ],
            $location->contentInfo
        );
    }

    /**
     * Test for the loadLocation() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationStructValues(Location $location)
    {
        $this->assertPropertiesCorrect(
            [
                'id' => $this->generateId('location', 5),
                'priority' => 0,
                'hidden' => false,
                'invisible' => false,
                'remoteId' => '3f6d92f8044aed134f32153517850f5a',
                'parentLocationId' => $this->generateId('location', 1),
                'pathString' => '/1/5/',
                'depth' => 1,
                'sortField' => 1,
                'sortOrder' => 1,
            ],
            $location
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $location->contentInfo
        );
        $this->assertEquals($this->generateId('object', 4), $location->contentInfo->id);

        // Check lazy loaded proxy on ->content
        $this->assertInstanceOf(
            Content::class,
            $content = $location->getContent()
        );
        $this->assertEquals(4, $content->contentInfo->id);
    }

    public function testLoadLocationPrioritizedLanguagesFallback()
    {
        $repository = $this->getRepository();

        // Add a language
        $this->createLanguage('nor-NO', 'Norsk');

        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $location = $locationService->loadLocation(5);

        // Translate "Users"
        $draft = $contentService->createContentDraft($location->contentInfo);
        $struct = $contentService->newContentUpdateStruct();
        $struct->setField('name', 'Brukere', 'nor-NO');
        $draft = $contentService->updateContent($draft->getVersionInfo(), $struct);
        $contentService->publishVersion($draft->getVersionInfo());

        // Load with priority language (fallback will be the old one)
        $location = $locationService->loadLocation(5, ['nor-NO']);

        $this->assertInstanceOf(
            Location::class,
            $location
        );
        self::assertEquals(5, $location->id);
        $this->assertInstanceOf(
            Content::class,
            $content = $location->getContent()
        );
        $this->assertEquals(4, $content->contentInfo->id);

        $this->assertEquals($content->getVersionInfo()->getName(), 'Brukere');
        $this->assertEquals($content->getVersionInfo()->getName('eng-US'), 'Users');
    }

    /**
     * Test that accessing lazy-loaded Content without a translation in the specific
     * not available language throws NotFoundException.
     */
    public function testLoadLocationThrowsNotFoundExceptionForNotAvailableContent(): void
    {
        $repository = $this->getRepository();

        $locationService = $repository->getLocationService();

        $this->createLanguage('pol-PL', 'Polski');

        $this->expectException(NotFoundException::class);

        // Note: relying on existing database fixtures to make test case more readable
        $locationService->loadLocation(60, ['pol-PL']);
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentLocationId = $this->generateId('location', 2342);
        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        // Throws exception, if Location with $nonExistentLocationId does not
        // exist
        $location = $locationService->loadLocation($nonExistentLocationId);
        /* END: Use Case */
    }

    /**
     * Test for the loadLocationList() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationList
     */
    public function testLoadLocationList(): void
    {
        $repository = $this->getRepository();

        // 5 is the ID of an existing location, 442 is a non-existing id
        $locationService = $repository->getLocationService();
        $locations = $locationService->loadLocationList([5, 442]);

        self::assertInternalType('iterable', $locations);
        self::assertCount(1, $locations);
        self::assertEquals([5], array_keys($locations));
        self::assertInstanceOf(Location::class, $locations[5]);
        self::assertEquals(5, $locations[5]->id);
    }

    /**
     * Test for the loadLocationList() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationList
     * @depends testLoadLocationList
     */
    public function testLoadLocationListPrioritizedLanguagesFallback(): void
    {
        $repository = $this->getRepository();

        $this->createLanguage('pol-PL', 'Polski');

        // 5 is the ID of an existing location, 442 is a non-existing id
        $locationService = $repository->getLocationService();
        $locations = $locationService->loadLocationList([5, 442], ['pol-PL'], false);

        self::assertInternalType('iterable', $locations);
        self::assertCount(0, $locations);
    }

    /**
     * Test for the loadLocationList() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationList
     * @depends testLoadLocationListPrioritizedLanguagesFallback
     */
    public function testLoadLocationListPrioritizedLanguagesFallbackAndAlwaysAvailable(): void
    {
        $repository = $this->getRepository();

        $this->createLanguage('pol-PL', 'Polski');

        // 5 is the ID of an existing location, 442 is a non-existing id
        $locationService = $repository->getLocationService();
        $locations = $locationService->loadLocationList([5, 442], ['pol-PL'], true);

        self::assertInternalType('iterable', $locations);
        self::assertCount(1, $locations);
        self::assertEquals([5], array_keys($locations));
        self::assertInstanceOf(Location::class, $locations[5]);
        self::assertEquals(5, $locations[5]->id);
    }

    /**
     * Test for the loadLocationList() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationList
     */
    public function testLoadLocationListWithRootLocationId()
    {
        $repository = $this->getRepository();

        // 1 is the ID of an root location
        $locationService = $repository->getLocationService();
        $locations = $locationService->loadLocationList([1]);

        self::assertInternalType('iterable', $locations);
        self::assertCount(1, $locations);
        self::assertEquals([1], array_keys($locations));
        self::assertInstanceOf(Location::class, $locations[1]);
        self::assertEquals(1, $locations[1]->id);
    }

    /**
     * Test for the loadLocationList() method.
     *
     * Ensures the list is returned in the same order as passed IDs array.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationList
     */
    public function testLoadLocationListInCorrectOrder()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $cachedLocationId = 2;
        $locationIdsToLoad = [43, $cachedLocationId, 5];

        // Call loadLocation to cache it in memory as it might possibly affect list order
        $locationService->loadLocation($cachedLocationId);

        $locations = $locationService->loadLocationList($locationIdsToLoad);
        $locationIds = array_column($locations, 'id');

        self::assertEquals($locationIdsToLoad, $locationIds);
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationByRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocationByRemoteId(
            '3f6d92f8044aed134f32153517850f5a'
        );
        /* END: Use Case */

        $this->assertEquals(
            $locationService->loadLocation($this->generateId('location', 5)),
            $location
        );
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationByRemoteIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        // Throws exception, since Location with remote ID does not exist
        $location = $locationService->loadLocationByRemoteId(
            'not-exists'
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocations() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testLoadLocations()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 4);
        /* BEGIN: Use Case */
        // $contentId contains the ID of an existing content object
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo($contentId);

        $locations = $locationService->loadLocations($contentInfo);
        /* END: Use Case */

        $this->assertInternalType('array', $locations);
        self::assertNotEmpty($locations);

        foreach ($locations as $location) {
            self::assertInstanceOf(Location::class, $location);
            self::assertEquals($contentInfo->id, $location->getContentInfo()->id);
        }

        return $locations;
    }

    /**
     * Test for the loadLocations() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     */
    public function testLoadLocationsContent(array $locations)
    {
        $this->assertEquals(1, count($locations));
        foreach ($locations as $loadedLocation) {
            self::assertInstanceOf(Location::class, $loadedLocation);
        }

        usort(
            $locations,
            static function ($a, $b) {
                return strcmp($a->id, $b->id);
            }
        );

        $this->assertEquals(
            [$this->generateId('location', 5)],
            array_map(
                static function (Location $location) {
                    return $location->id;
                },
                $locations
            )
        );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     */
    public function testLoadLocationsLimitedSubtree()
    {
        $repository = $this->getRepository();

        $originalLocationId = $this->generateId('location', 54);
        $originalParentLocationId = $this->generateId('location', 48);
        $newParentLocationId = $this->generateId('location', 43);
        /* BEGIN: Use Case */
        // $originalLocationId is the ID of an existing location
        // $originalParentLocationId is the ID of the parent location of
        //     $originalLocationId
        // $newParentLocationId is the ID of an existing location outside the tree
        // of $originalLocationId and $originalParentLocationId
        $locationService = $repository->getLocationService();

        // Location at "/1/48/54"
        $originalLocation = $locationService->loadLocation($originalLocationId);

        // Create location under "/1/43/"
        $locationCreate = $locationService->newLocationCreateStruct($newParentLocationId);
        $locationService->createLocation(
            $originalLocation->contentInfo,
            $locationCreate
        );

        $findRootLocation = $locationService->loadLocation($originalParentLocationId);

        // Returns an array with only $originalLocation
        $locations = $locationService->loadLocations(
            $originalLocation->contentInfo,
            $findRootLocation
        );
        /* END: Use Case */

        $this->assertInternalType('array', $locations);

        return $locations;
    }

    /**
     * Test for the loadLocations() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationsLimitedSubtree
     */
    public function testLoadLocationsLimitedSubtreeContent(array $locations)
    {
        $this->assertEquals(1, count($locations));

        $this->assertEquals(
            $this->generateId('location', 54),
            reset($locations)->id
        );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // Create new content, which is not published
        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $contentCreate = $contentService->newContentCreateStruct($folderType, 'eng-US');
        $contentCreate->setField('name', 'New Folder');
        $content = $contentService->createContent($contentCreate);

        // Throws Exception, since $content has no published version, yet
        $locationService->loadLocations(
            $content->contentInfo
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocations() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateExceptionLimitedSubtree()
    {
        $repository = $this->getRepository();

        $someLocationId = $this->generateId('location', 2);
        /* BEGIN: Use Case */
        // $someLocationId is the ID of an existing location
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // Create new content, which is not published
        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $contentCreate = $contentService->newContentCreateStruct($folderType, 'eng-US');
        $contentCreate->setField('name', 'New Folder');
        $content = $contentService->createContent($contentCreate);

        $findRootLocation = $locationService->loadLocation($someLocationId);

        // Throws Exception, since $content has no published version, yet
        $locationService->loadLocations(
            $content->contentInfo,
            $findRootLocation
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationChildren
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationChildren()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);

        $childLocations = $locationService->loadLocationChildren($location);
        /* END: Use Case */

        $this->assertInstanceOf(LocationList::class, $childLocations);
        $this->assertInternalType('array', $childLocations->locations);
        $this->assertNotEmpty($childLocations->locations);
        $this->assertInternalType('int', $childLocations->totalCount);

        foreach ($childLocations->locations as $childLocation) {
            $this->assertInstanceOf(Location::class, $childLocation);
            $this->assertEquals($location->id, $childLocation->parentLocationId);
        }

        return $childLocations;
    }

    /**
     * Test loading parent Locations for draft Content.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadParentLocationsForDraftContent
     */
    public function testLoadParentLocationsForDraftContent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        // prepare locations
        $locationCreateStructs = [
            $locationService->newLocationCreateStruct(2),
            $locationService->newLocationCreateStruct(5),
        ];

        // Create new content
        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $contentCreate = $contentService->newContentCreateStruct($folderType, 'eng-US');
        $contentCreate->setField('name', 'New Folder');
        $contentDraft = $contentService->createContent($contentCreate, $locationCreateStructs);

        // Test loading parent Locations
        $locations = $locationService->loadParentLocationsForDraftContent($contentDraft->versionInfo);

        self::assertCount(2, $locations);
        foreach ($locations as $location) {
            // test it is one of the given parent locations
            self::assertTrue($location->id === 2 || $location->id === 5);
        }

        return $contentDraft;
    }

    /**
     * Test that trying to load parent Locations throws Exception if Content is not a draft.
     *
     * @depends testLoadParentLocationsForDraftContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $contentDraft
     */
    public function testLoadParentLocationsForDraftContentThrowsBadStateException(Content $contentDraft)
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessageRegExp('/has been already published/');

        $repository = $this->getRepository(false);
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $content = $contentService->publishVersion($contentDraft->versionInfo);

        $locationService->loadParentLocationsForDraftContent($content->versionInfo);
    }

    /**
     * Test for the getLocationChildCount() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::getLocationChildCount()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testGetLocationChildCount()
    {
        // $locationId is the ID of an existing location
        $locationService = $this->getRepository()->getLocationService();

        $this->assertSame(
            5,
            $locationService->getLocationChildCount(
                $locationService->loadLocation($this->generateId('location', 5))
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenData(LocationList $locations)
    {
        $this->assertEquals(5, count($locations->locations));
        $this->assertEquals(5, $locations->totalCount);

        foreach ($locations->locations as $location) {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $location
            );
        }

        $this->assertEquals(
            [
                $this->generateId('location', 12),
                $this->generateId('location', 13),
                $this->generateId('location', 14),
                $this->generateId('location', 44),
                $this->generateId('location', 61),
            ],
            array_map(
                function (Location $location) {
                    return $location->id;
                },
                $locations->locations
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenWithOffset()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);

        $childLocations = $locationService->loadLocationChildren($location, 2);
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationList', $childLocations);
        $this->assertInternalType('array', $childLocations->locations);
        $this->assertInternalType('int', $childLocations->totalCount);

        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationList $locations
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildrenWithOffset
     */
    public function testLoadLocationChildrenDataWithOffset(LocationList $locations)
    {
        $this->assertEquals(3, count($locations->locations));
        $this->assertEquals(5, $locations->totalCount);

        foreach ($locations->locations as $location) {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $location
            );
        }

        $this->assertEquals(
            [
                $this->generateId('location', 14),
                $this->generateId('location', 44),
                $this->generateId('location', 61),
            ],
            array_map(
                function (Location $location) {
                    return $location->id;
                },
                $locations->locations
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenWithOffsetAndLimit()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($locationId);

        $childLocations = $locationService->loadLocationChildren($location, 2, 2);
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationList', $childLocations);
        $this->assertInternalType('array', $childLocations->locations);
        $this->assertInternalType('int', $childLocations->totalCount);

        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildrenWithOffsetAndLimit
     */
    public function testLoadLocationChildrenDataWithOffsetAndLimit(LocationList $locations)
    {
        $this->assertEquals(2, count($locations->locations));
        $this->assertEquals(5, $locations->totalCount);

        foreach ($locations->locations as $location) {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $location
            );
        }

        $this->assertEquals(
            [
                $this->generateId('location', 14),
                $this->generateId('location', 44),
            ],
            array_map(
                function (Location $location) {
                    return $location->id;
                },
                $locations->locations
            )
        );
    }

    /**
     * Test for the newLocationUpdateStruct() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::newLocationUpdateStruct
     */
    public function testNewLocationUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $updateStruct = $locationService->newLocationUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            LocationUpdateStruct::class,
            $updateStruct
        );

        $this->assertPropertiesCorrect(
            [
                'priority' => null,
                'remoteId' => null,
                'sortField' => null,
                'sortOrder' => null,
            ],
            $updateStruct
        );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testUpdateLocation()
    {
        $repository = $this->getRepository();

        $originalLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $originalLocationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation($originalLocationId);

        $updateStruct = $locationService->newLocationUpdateStruct();
        $updateStruct->priority = 3;
        $updateStruct->remoteId = 'c7adcbf1e96bc29bca28c2d809d0c7ef69272651';
        $updateStruct->sortField = Location::SORT_FIELD_PRIORITY;
        $updateStruct->sortOrder = Location::SORT_ORDER_DESC;

        $updatedLocation = $locationService->updateLocation($originalLocation, $updateStruct);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $updatedLocation
        );

        return [
            'originalLocation' => $originalLocation,
            'updateStruct' => $updateStruct,
            'updatedLocation' => $updatedLocation,
        ];
    }

    /**
     * Test for the updateLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUpdateLocation
     */
    public function testUpdateLocationStructValues(array $data)
    {
        $originalLocation = $data['originalLocation'];
        $updateStruct = $data['updateStruct'];
        $updatedLocation = $data['updatedLocation'];

        $this->assertPropertiesCorrect(
            [
                'id' => $originalLocation->id,
                'priority' => $updateStruct->priority,
                'hidden' => $originalLocation->hidden,
                'invisible' => $originalLocation->invisible,
                'remoteId' => $updateStruct->remoteId,
                'contentInfo' => $originalLocation->contentInfo,
                'parentLocationId' => $originalLocation->parentLocationId,
                'pathString' => $originalLocation->pathString,
                'depth' => $originalLocation->depth,
                'sortField' => $updateStruct->sortField,
                'sortOrder' => $updateStruct->sortOrder,
            ],
            $updatedLocation
        );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testUpdateLocationWithSameRemoteId()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId and remote ID is the IDs of the same, existing location
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation($locationId);

        $updateStruct = $locationService->newLocationUpdateStruct();

        // Remote ID of an existing location with the same locationId
        $updateStruct->remoteId = $originalLocation->remoteId;

        // Sets one of the properties to be able to confirm location gets updated, here: priority
        $updateStruct->priority = 2;

        $location = $locationService->updateLocation($originalLocation, $updateStruct);

        // Checks that the location was updated
        $this->assertEquals(2, $location->priority);

        // Checks that remoteId remains the same
        $this->assertEquals($originalLocation->remoteId, $location->remoteId);
        /* END: Use Case */
    }

    /**
     * Test for the updateLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateLocationThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId and remoteId is the IDs of an existing, but not the same, location
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation($locationId);

        $updateStruct = $locationService->newLocationUpdateStruct();

        // Remote ID of an existing location with a different locationId
        $updateStruct->remoteId = 'f3e90596361e31d496d4026eb624c983';

        // Throws exception, since remote ID is already taken
        $locationService->updateLocation($originalLocation, $updateStruct);
        /* END: Use Case */
    }

    /**
     * Test for the updateLocation() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     * @dataProvider dataProviderForOutOfRangeLocationPriority
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateLocationThrowsInvalidArgumentExceptionPriorityIsOutOfRange($priority)
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId and remoteId is the IDs of an existing, but not the same, location
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation($locationId);

        $updateStruct = $locationService->newLocationUpdateStruct();

        // Priority value is out of range
        $updateStruct->priority = $priority;

        // Throws exception, since remote ID is already taken
        $locationService->updateLocation($originalLocation, $updateStruct);
        /* END: Use Case */
    }

    /**
     * Test for the updateLocation() method.
     * Ref EZP-23302: Update Location fails if no change is performed with the update.
     *
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testUpdateLocationTwice()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();
        $repository->setCurrentUser($repository->getUserService()->loadUser(14));

        $originalLocation = $locationService->loadLocation($locationId);

        $updateStruct = $locationService->newLocationUpdateStruct();
        $updateStruct->priority = 42;

        $updatedLocation = $locationService->updateLocation($originalLocation, $updateStruct);

        // Repeated update with the same, unchanged struct
        $secondUpdatedLocation = $locationService->updateLocation($updatedLocation, $updateStruct);
        /* END: Use Case */

        $this->assertEquals($updatedLocation->priority, 42);
        $this->assertEquals($secondUpdatedLocation->priority, 42);
    }

    /**
     * Test for the swapLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testSwapLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);

        $mediaContentInfo = $locationService->loadLocation($mediaLocationId)->getContentInfo();
        $demoDesignContentInfo = $locationService->loadLocation($demoDesignLocationId)->getContentInfo();

        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        $mediaLocation = $locationService->loadLocation($mediaLocationId);
        $demoDesignLocation = $locationService->loadLocation($demoDesignLocationId);

        // Swaps the content referred to by the locations
        $locationService->swapLocation($mediaLocation, $demoDesignLocation);
        /* END: Use Case */

        // Reload Locations, IDs swapped
        $demoDesignLocation = $locationService->loadLocation($mediaLocationId);
        $mediaLocation = $locationService->loadLocation($demoDesignLocationId);

        // Assert Location's Content is updated
        $this->assertEquals(
            $mediaContentInfo->id,
            $mediaLocation->getContentInfo()->id
        );
        $this->assertEquals(
            $demoDesignContentInfo->id,
            $demoDesignLocation->getContentInfo()->id
        );

        // Assert URL aliases are updated
        $this->assertEquals(
            $mediaLocation->id,
            $repository->getURLAliasService()->lookup('/Design/Media')->destination
        );
        $this->assertEquals(
            $demoDesignLocation->id,
            $repository->getURLAliasService()->lookup('/eZ-Publish-Demo-Design-without-demo-content')->destination
        );
    }

    /**
     * Test for the swapLocation() method with custom aliases.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSwapLocationForContentWithCustomUrlAliases(): void
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();
        $this->createLanguage('pol-PL', 'Polski');

        $folder1 = $this->createFolder(['eng-GB' => 'Folder1', 'pol-PL' => 'Folder1'], 2);
        $folder2 = $this->createFolder(['eng-GB' => 'Folder2'], 2);
        $location1 = $locationService->loadLocation($folder1->contentInfo->mainLocationId);
        $location2 = $locationService->loadLocation($folder2->contentInfo->mainLocationId);

        $urlAlias = $urlAliasService->createUrlAlias($location1, '/custom-location1', 'eng-GB', false, true);
        $urlAliasService->createUrlAlias($location1, '/custom-location1', 'pol-PL', false, true);
        $urlAliasService->createUrlAlias($location2, '/custom-location2', 'eng-GB', false, true);
        $location1UrlAliases = $urlAliasService->listLocationAliases($location1);
        $location2UrlAliases = $urlAliasService->listLocationAliases($location2);

        $locationService->swapLocation($location1, $location2);
        $location1 = $locationService->loadLocation($location1->contentInfo->mainLocationId);
        $location2 = $locationService->loadLocation($location2->contentInfo->mainLocationId);

        $location1UrlAliasesAfterSwap = $urlAliasService->listLocationAliases($location1);
        $location2UrlAliasesAfterSwap = $urlAliasService->listLocationAliases($location2);

        $keyUrlAlias = array_search($urlAlias->id, array_column($location1UrlAliasesAfterSwap, 'id'));

        self::assertEquals($folder1->id, $location2->contentInfo->id);
        self::assertEquals($folder2->id, $location1->contentInfo->id);
        self::assertNotEquals($location1UrlAliases, $location1UrlAliasesAfterSwap);
        self::assertEquals($location2UrlAliases, $location2UrlAliasesAfterSwap);
        self::assertEquals(['eng-GB'], $location1UrlAliasesAfterSwap[$keyUrlAlias]->languageCodes);
    }

    /**
     * Test swapping secondary Location with main Location.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     *
     * @see https://jira.ez.no/browse/EZP-28663
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return int[]
     */
    public function testSwapLocationForMainAndSecondaryLocation(): array
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $folder1 = $this->createFolder(['eng-GB' => 'Folder1'], 2);
        $folder2 = $this->createFolder(['eng-GB' => 'Folder2'], 2);
        $folder3 = $this->createFolder(['eng-GB' => 'Folder3'], 2);

        $primaryLocation = $locationService->loadLocation($folder1->contentInfo->mainLocationId);
        $parentLocation = $locationService->loadLocation($folder2->contentInfo->mainLocationId);
        $secondaryLocation = $locationService->createLocation(
            $folder1->contentInfo,
            $locationService->newLocationCreateStruct($parentLocation->id)
        );

        $targetLocation = $locationService->loadLocation($folder3->contentInfo->mainLocationId);

        // perform sanity checks
        $this->assertContentHasExpectedLocations([$primaryLocation, $secondaryLocation], $folder1);

        // begin use case
        $locationService->swapLocation($secondaryLocation, $targetLocation);

        // test results
        $primaryLocation = $locationService->loadLocation($primaryLocation->id);
        $secondaryLocation = $locationService->loadLocation($secondaryLocation->id);
        $targetLocation = $locationService->loadLocation($targetLocation->id);

        self::assertEquals($folder1->id, $primaryLocation->contentInfo->id);
        self::assertEquals($folder1->id, $targetLocation->contentInfo->id);
        self::assertEquals($folder3->id, $secondaryLocation->contentInfo->id);

        $this->assertContentHasExpectedLocations([$primaryLocation, $targetLocation], $folder1);

        self::assertEquals(
            $folder1,
            $contentService->loadContent($folder1->id)
        );

        self::assertEquals(
            $folder2,
            $contentService->loadContent($folder2->id)
        );

        // only in case of Folder 3, main location id changed due to swap
        self::assertEquals(
            $secondaryLocation->id,
            $contentService->loadContent($folder3->id)->contentInfo->mainLocationId
        );

        return [$folder1, $folder2, $folder3];
    }

    /**
     * Compare Ids of expected and loaded Locations for the given Content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $expectedLocations
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    private function assertContentHasExpectedLocations(array $expectedLocations, Content $content)
    {
        $repository = $this->getRepository(false);
        $locationService = $repository->getLocationService();

        $expectedLocationIds = array_map(
            function (Location $location) {
                return (int)$location->id;
            },
            $expectedLocations
        );

        $actualLocationsIds = array_map(
            function (Location $location) {
                return $location->id;
            },
            $locationService->loadLocations($content->contentInfo)
        );
        self::assertCount(count($expectedLocations), $actualLocationsIds);

        // perform unordered equality assertion
        self::assertEquals(
            $expectedLocationIds,
            $actualLocationsIds,
            sprintf(
                'Content %d contains Locations %s, but expected: %s',
                $content->id,
                implode(', ', $actualLocationsIds),
                implode(', ', $expectedLocationIds)
            ),
            0.0,
            10,
            true
        );
    }

    /**
     * @depends testSwapLocationForMainAndSecondaryLocation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content[] $contentItems Content items created by testSwapLocationForSecondaryLocation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSwapLocationDoesNotCorruptSearchResults(array $contentItems)
    {
        $repository = $this->getRepository(false);
        $searchService = $repository->getSearchService();

        $this->refreshSearch($repository);

        $contentIds = array_map(
            function (Content $content) {
                return $content->id;
            },
            $contentItems
        );

        $query = new Query();
        $query->filter = new Query\Criterion\ContentId($contentIds);

        $searchResult = $searchService->findContent($query);

        self::assertEquals(count($contentItems), $searchResult->totalCount);
        self::assertEquals(
            $searchResult->totalCount,
            count($searchResult->searchHits),
            'Total count of search result hits does not match the actual number of found results'
        );
        $foundContentIds = array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject->id;
            },
            $searchResult->searchHits
        );
        sort($contentIds);
        sort($foundContentIds);
        self::assertSame(
            $contentIds,
            $foundContentIds,
            'Got different than expected Content item Ids'
        );
    }

    /**
     * Test swapping two secondary (non-main) Locations.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSwapLocationForSecondaryLocations()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $folder1 = $this->createFolder(['eng-GB' => 'Folder1'], 2);
        $folder2 = $this->createFolder(['eng-GB' => 'Folder2'], 2);
        $parentFolder1 = $this->createFolder(['eng-GB' => 'Parent1'], 2);
        $parentFolder2 = $this->createFolder(['eng-GB' => 'Parent2'], 2);

        $parentLocation1 = $locationService->loadLocation($parentFolder1->contentInfo->mainLocationId);
        $parentLocation2 = $locationService->loadLocation($parentFolder2->contentInfo->mainLocationId);
        $secondaryLocation1 = $locationService->createLocation(
            $folder1->contentInfo,
            $locationService->newLocationCreateStruct($parentLocation1->id)
        );
        $secondaryLocation2 = $locationService->createLocation(
            $folder2->contentInfo,
            $locationService->newLocationCreateStruct($parentLocation2->id)
        );

        // begin use case
        $locationService->swapLocation($secondaryLocation1, $secondaryLocation2);

        // test results
        $secondaryLocation1 = $locationService->loadLocation($secondaryLocation1->id);
        $secondaryLocation2 = $locationService->loadLocation($secondaryLocation2->id);

        self::assertEquals($folder2->id, $secondaryLocation1->contentInfo->id);
        self::assertEquals($folder1->id, $secondaryLocation2->contentInfo->id);

        self::assertEquals(
            $folder1,
            $contentService->loadContent($folder1->id)
        );

        self::assertEquals(
            $folder2,
            $contentService->loadContent($folder2->id)
        );
    }

    /**
     * Test swapping Main Location of a Content with another one updates Content item Main Location.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     */
    public function testSwapLocationUpdatesMainLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $mainLocationParentId = 60;
        $secondaryLocationId = 43;

        $publishedContent = $this->publishContentWithParentLocation(
            'Content for Swap Location Test', $mainLocationParentId
        );

        // sanity check
        $mainLocation = $locationService->loadLocation($publishedContent->contentInfo->mainLocationId);
        self::assertEquals($mainLocationParentId, $mainLocation->parentLocationId);

        // load another pre-existing location
        $secondaryLocation = $locationService->loadLocation($secondaryLocationId);

        // swap the Main Location with a secondary one
        $locationService->swapLocation($mainLocation, $secondaryLocation);

        // check if Main Location has been updated
        $mainLocation = $locationService->loadLocation($secondaryLocation->id);
        self::assertEquals($publishedContent->contentInfo->id, $mainLocation->contentInfo->id);
        self::assertEquals($mainLocation->id, $mainLocation->contentInfo->mainLocationId);

        $reloadedContent = $contentService->loadContentByContentInfo($publishedContent->contentInfo);
        self::assertEquals($mainLocation->id, $reloadedContent->contentInfo->mainLocationId);
    }

    /**
     * Test if location swap affects related bookmarks.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     */
    public function testBookmarksAreSwappedAfterSwapLocation()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();
        $bookmarkService = $repository->getBookmarkService();

        $mediaLocation = $locationService->loadLocation($mediaLocationId);
        $demoDesignLocation = $locationService->loadLocation($demoDesignLocationId);

        // Bookmark locations
        $bookmarkService->createBookmark($mediaLocation);
        $bookmarkService->createBookmark($demoDesignLocation);

        $beforeSwap = $bookmarkService->loadBookmarks();

        // Swaps the content referred to by the locations
        $locationService->swapLocation($mediaLocation, $demoDesignLocation);

        $afterSwap = $bookmarkService->loadBookmarks();
        /* END: Use Case */

        $this->assertEquals($beforeSwap->items[0]->id, $afterSwap->items[1]->id);
        $this->assertEquals($beforeSwap->items[1]->id, $afterSwap->items[0]->id);
    }

    /**
     * Test for the hideLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testHideLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation($locationId);

        $hiddenLocation = $locationService->hideLocation($visibleLocation);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $hiddenLocation
        );

        $this->assertTrue(
            $hiddenLocation->hidden,
            sprintf(
                'Location with ID "%s" not hidden.',
                $hiddenLocation->id
            )
        );

        $this->refreshSearch($repository);

        foreach ($locationService->loadLocationChildren($hiddenLocation)->locations as $child) {
            $this->assertSubtreeProperties(
                ['invisible' => true],
                $child
            );
        }
    }

    /**
     * Assert that $expectedValues are set in the subtree starting at $location.
     *
     * @param array $expectedValues
     * @param Location $location
     */
    protected function assertSubtreeProperties(array $expectedValues, Location $location, $stopId = null)
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        if ($location->id === $stopId) {
            return;
        }

        foreach ($expectedValues as $propertyName => $propertyValue) {
            $this->assertEquals(
                $propertyValue,
                $location->$propertyName
            );

            foreach ($locationService->loadLocationChildren($location)->locations as $child) {
                $this->assertSubtreeProperties($expectedValues, $child);
            }
        }
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testHideLocation
     */
    public function testUnhideLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation($locationId);
        $hiddenLocation = $locationService->hideLocation($visibleLocation);

        $unHiddenLocation = $locationService->unhideLocation($hiddenLocation);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $unHiddenLocation
        );

        $this->assertFalse(
            $unHiddenLocation->hidden,
            sprintf(
                'Location with ID "%s" not unhidden.',
                $unHiddenLocation->id
            )
        );

        $this->refreshSearch($repository);

        foreach ($locationService->loadLocationChildren($unHiddenLocation)->locations as $child) {
            $this->assertSubtreeProperties(
                ['invisible' => false],
                $child
            );
        }
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUnhideLocation
     */
    public function testUnhideLocationNotUnhidesHiddenSubtree()
    {
        $repository = $this->getRepository();

        $higherLocationId = $this->generateId('location', 5);
        $lowerLocationId = $this->generateId('location', 13);
        /* BEGIN: Use Case */
        // $higherLocationId is the ID of a location
        // $lowerLocationId is the ID of a location below $higherLocationId
        $locationService = $repository->getLocationService();

        $higherLocation = $locationService->loadLocation($higherLocationId);
        $hiddenHigherLocation = $locationService->hideLocation($higherLocation);

        $lowerLocation = $locationService->loadLocation($lowerLocationId);
        $hiddenLowerLocation = $locationService->hideLocation($lowerLocation);

        $unHiddenHigherLocation = $locationService->unhideLocation($hiddenHigherLocation);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $unHiddenHigherLocation
        );

        $this->assertFalse(
            $unHiddenHigherLocation->hidden,
            sprintf(
                'Location with ID "%s" not unhidden.',
                $unHiddenHigherLocation->id
            )
        );

        $this->refreshSearch($repository);

        foreach ($locationService->loadLocationChildren($unHiddenHigherLocation)->locations as $child) {
            $this->assertSubtreeProperties(
                ['invisible' => false],
                $child,
                $this->generateId('location', 13)
            );
        }

        $stillHiddenLocation = $locationService->loadLocation($this->generateId('location', 13));
        $this->assertTrue(
            $stillHiddenLocation->hidden,
            sprintf(
                'Hidden sub-location with ID %s accidentally unhidden.',
                $stillHiddenLocation->id
            )
        );
        foreach ($locationService->loadLocationChildren($stillHiddenLocation)->locations as $child) {
            $this->assertSubtreeProperties(
                ['invisible' => true],
                $child
            );
        }
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testDeleteLocation()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the location of the
        // "Media" location in an eZ Publish demo installation
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($mediaLocationId);

        $locationService->deleteLocation($location);
        /* END: Use Case */

        try {
            $locationService->loadLocation($mediaLocationId);
            $this->fail("Location $mediaLocationId not deleted.");
        } catch (NotFoundException $e) {
        }

        // The following IDs are IDs of child locations of $mediaLocationId location
        // ( Media/Images, Media/Files, Media/Multimedia respectively )
        foreach ([51, 52, 53] as $childLocationId) {
            try {
                $locationService->loadLocation($this->generateId('location', $childLocationId));
                $this->fail("Location $childLocationId not deleted.");
            } catch (NotFoundException $e) {
            }
        }

        // The following IDs are IDs of content below $mediaLocationId location
        // ( Media/Images, Media/Files, Media/Multimedia respectively )
        $contentService = $this->getRepository()->getContentService();
        foreach ([49, 50, 51] as $childContentId) {
            try {
                $contentService->loadContentInfo($this->generateId('object', $childContentId));
                $this->fail("Content $childContentId not deleted.");
            } catch (NotFoundException $e) {
            }
        }
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testDeleteLocation
     */
    public function testDeleteLocationDecrementsChildCountOnParent()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the location of the
        // "Media" location in an eZ Publish demo installation

        $locationService = $repository->getLocationService();

        // Load the current the user group location
        $location = $locationService->loadLocation($mediaLocationId);

        // Load the parent location
        $parentLocation = $locationService->loadLocation(
            $location->parentLocationId
        );

        // Get child count
        $childCountBefore = $locationService->getLocationChildCount($parentLocation);

        // Delete the user group location
        $locationService->deleteLocation($location);

        $this->refreshSearch($repository);

        // Reload parent location
        $parentLocation = $locationService->loadLocation(
            $location->parentLocationId
        );

        // This will be $childCountBefore - 1
        $childCountAfter = $locationService->getLocationChildCount($parentLocation);
        /* END: Use Case */

        $this->assertEquals($childCountBefore - 1, $childCountAfter);
    }

    /**
     * Test for the deleteLocation() method.
     *
     * Related issue: EZP-21904
     *
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteContentObjectLastLocation()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use case */
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentTypeService = $repository->getContentTypeService();
        $urlAliasService = $repository->getURLAliasService();

        // prepare Content object
        $createStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );
        $createStruct->setField('name', 'Test folder');

        // creata Content object
        $content = $contentService->publishVersion(
            $contentService->createContent(
                $createStruct,
                [$locationService->newLocationCreateStruct(2)]
            )->versionInfo
        );

        // delete location
        $locationService->deleteLocation(
            $locationService->loadLocation(
                $urlAliasService->lookup('/Test-folder')->destination
            )
        );

        // this should throw a not found exception
        $contentService->loadContent($content->versionInfo->contentInfo->id);
        /* END: Use case*/
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @covers  \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testDeleteLocation
     */
    public function testDeleteLocationDeletesRelatedBookmarks()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId('location', 43);
        $childLocationId = $this->generateId('location', 53);

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();
        $bookmarkService = $repository->getBookmarkService();

        // Load location
        $childLocation = $locationService->loadLocation($childLocationId);
        // Add location to bookmarks
        $bookmarkService->createBookmark($childLocation);
        // Load parent location
        $parentLocation = $locationService->loadLocation($parentLocationId);
        // Delete parent location
        $locationService->deleteLocation($parentLocation);
        /* END: Use Case */

        // Location isn't bookmarked anymore
        foreach ($bookmarkService->loadBookmarks(0, 9999) as $bookmarkedLocation) {
            $this->assertNotEquals($childLocation->id, $bookmarkedLocation->id);
        }
    }

    /**
     * @covers \eZ\Publish\API\Repository\LocationService::deleteLocation
     */
    public function testDeleteUnusedLocationWhichPreviousHadContentWithRelativeAlias(): void
    {
        $repository = $this->getRepository(false);

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $originalFolder = $this->createFolder(['eng-GB' => 'Original folder'], 2);
        $newFolder = $this->createFolder(['eng-GB' => 'New folder'], 2);
        $originalFolderLocationId = $originalFolder->contentInfo->mainLocationId;

        $forum = $contentService->publishVersion(
            $contentService->createContent(
                $this->createForumStruct('Some forum'),
                [
                    $locationService->newLocationCreateStruct($originalFolderLocationId),
                ]
            )->versionInfo
        );

        $forumMainLocation = $locationService->loadLocation(
            $forum->contentInfo->mainLocationId
        );

        $customRelativeAliasPath = '/Original-folder/some-forum-alias';

        $urlAliasService->createUrlAlias(
            $forumMainLocation,
            $customRelativeAliasPath,
            'eng-GB',
            true,
            true
        );

        $locationService->moveSubtree(
            $forumMainLocation,
            $locationService->loadLocation(
                $newFolder->contentInfo->mainLocationId
            )
        );

        $this->assertAliasExists(
            $customRelativeAliasPath,
            $forumMainLocation,
            $urlAliasService
        );

        $urlAliasService->lookup($customRelativeAliasPath);

        $locationService->deleteLocation(
            $locationService->loadLocation(
                $originalFolder->contentInfo->mainLocationId
            )
        );

        $this->assertAliasExists(
            $customRelativeAliasPath,
            $forumMainLocation,
            $urlAliasService
        );

        $urlAliasService->lookup($customRelativeAliasPath);
    }

    /**
     * Test for the copySubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testCopySubtree()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // Copy location "Media" to "Demo Design"
        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $copiedLocation
        );

        $this->assertPropertiesCorrect(
            [
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => $newParentLocation->pathString . $this->parseId('location', $copiedLocation->id) . '/',
            ],
            $copiedLocation
        );

        $this->assertDefaultContentStates($copiedLocation->contentInfo);
    }

    /**
     * Test for the copySubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testCopySubtreeWithAliases()
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();

        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation
        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);

        $locationService = $repository->getLocationService();
        $locationToCopy = $locationService->loadLocation($mediaLocationId);
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        $expectedSubItemAliases = [
            '/Design/Plain-site/Media/Multimedia',
            '/Design/Plain-site/Media/Images',
            '/Design/Plain-site/Media/Files',
        ];

        $this->assertAliasesBeforeCopy($urlAliasService, $expectedSubItemAliases);

        // Copy location "Media" to "Design"
        $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );

        $this->assertGeneratedAliases($urlAliasService, $expectedSubItemAliases);
    }

    /**
     * @covers \eZ\Publish\API\Repository\LocationService::copySubtree
     */
    public function testCopySubtreeWithTranslatedContent(): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $mediaLocationId = $this->generateId('location', 43);
        $filesLocationId = $this->generateId('location', 52);
        $demoDesignLocationId = $this->generateId('location', 56);

        $locationToCopy = $locationService->loadLocation($mediaLocationId);
        $filesLocation = $locationService->loadLocation($filesLocationId);
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // translating the 'middle' folder
        $translatedDraft = $contentService->createContentDraft($filesLocation->contentInfo);
        $contentUpdateStruct = new ContentUpdateStruct([
            'initialLanguageCode' => 'ger-DE',
            'fields' => $translatedDraft->getFields(),
        ]);
        $contentUpdateStruct->setField('short_name', 'FilesGER', 'ger-DE');
        $translatedContent = $contentService->updateContent($translatedDraft->versionInfo, $contentUpdateStruct);
        $contentService->publishVersion($translatedContent->versionInfo);

        // creating additional content under translated folder
        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreate->setField('name', 'My folder');
        $content = $contentService->createContent(
            $contentCreate,
            [new LocationCreateStruct(['parentLocationId' => $filesLocationId])]
        );
        $contentService->publishVersion($content->versionInfo);

        $expectedSubItemAliases = [
            '/Design/Plain-site/Media/Multimedia',
            '/Design/Plain-site/Media/Images',
            '/Design/Plain-site/Media/Files',
            '/Design/Plain-site/Media/Files/my-folder',
        ];

        $this->assertAliasesBeforeCopy($urlAliasService, $expectedSubItemAliases);

        $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );

        $this->assertGeneratedAliases($urlAliasService, $expectedSubItemAliases);
    }

    /**
     * Asserts that given Content has default ContentStates.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    private function assertDefaultContentStates(ContentInfo $contentInfo)
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroups = $objectStateService->loadObjectStateGroups();

        foreach ($objectStateGroups as $objectStateGroup) {
            $contentState = $objectStateService->getContentState($contentInfo, $objectStateGroup);
            foreach ($objectStateService->loadObjectStates($objectStateGroup) as $objectState) {
                // Only check the first object state which is the default one.
                $this->assertEquals(
                    $objectState,
                    $contentState
                );
                break;
            }
        }
    }

    /**
     * Test for the copySubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeUpdatesSubtreeProperties()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $locationToCopy = $locationService->loadLocation($this->generateId('location', 43));

        // Load Subtree properties before copy
        $expected = $this->loadSubtreeProperties($locationToCopy);

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // Copy location "Media" to "Demo Design"
        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */

        $beforeIds = [];
        foreach ($expected as $properties) {
            $beforeIds[] = $properties['id'];
        }

        $this->refreshSearch($repository);

        // Load Subtree properties after copy
        $actual = $this->loadSubtreeProperties($copiedLocation);

        $this->assertEquals(count($expected), count($actual));

        foreach ($actual as $properties) {
            $this->assertNotContains($properties['id'], $beforeIds);
            $this->assertStringStartsWith(
                $newParentLocation->pathString . $this->parseId('location', $copiedLocation->id) . '/',
                $properties['pathString']
            );
            $this->assertStringEndsWith(
                '/' . $this->parseId('location', $properties['id']) . '/',
                $properties['pathString']
            );
        }
    }

    /**
     * Test for the copySubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeIncrementsChildCountOfNewParent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $childCountBefore = $locationService->getLocationChildCount($locationService->loadLocation(56));

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // Copy location "Media" to "Demo Design"
        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */

        $this->refreshSearch($repository);

        $childCountAfter = $locationService->getLocationChildCount($locationService->loadLocation($demoDesignLocationId));

        $this->assertEquals($childCountBefore + 1, $childCountAfter);
    }

    /**
     * @covers \eZ\Publish\API\Repository\LocationService::copySubtree()
     */
    public function testCopySubtreeWithInvisibleChild(): void
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        // Hide child Location
        $locationService->hideLocation($locationService->loadLocation($this->generateId('location', 53)));

        $this->refreshSearch($repository);

        $locationToCopy = $locationService->loadLocation($this->generateId('location', 43));

        $expected = $this->loadSubtreeProperties($locationToCopy);

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        $locationService = $repository->getLocationService();

        $locationToCopy = $locationService->loadLocation($mediaLocationId);

        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );

        $this->refreshSearch($repository);

        // Load Subtree properties after copy
        $actual = $this->loadSubtreeProperties($copiedLocation);

        self::assertEquals(count($expected), count($actual));

        foreach ($actual as $key => $properties) {
            self::assertEquals($expected[$key]['hidden'], $properties['hidden']);
            self::assertEquals($expected[$key]['invisible'], $properties['invisible']);
        }
    }

    /**
     * Test for the copySubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $communityLocationId = $this->generateId('location', 5);
        /* BEGIN: Use Case */
        // $communityLocationId is the ID of the "Community" page location in
        // an eZ Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation($communityLocationId);

        // Use a child as new parent
        $childLocations = $locationService->loadLocationChildren($locationToCopy)->locations;
        $newParentLocation = end($childLocations);

        // This call will fail with an "InvalidArgumentException", because the
        // new parent is a child location of the subtree to copy.
        $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testMoveSubtree(): void
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);

        // Move location from "Home" to "Media"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation($demoDesignLocationId);
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            [
                'hidden' => false,
                'invisible' => false,
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => $newParentLocation->pathString . $this->parseId('location', $movedLocation->id) . '/',
            ],
            $movedLocation
        );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testMoveSubtreeToLocationWithoutContent(): void
    {
        $repository = $this->getRepository();

        $rootLocationId = $this->generateId('location', 1);
        $demoDesignLocationId = $this->generateId('location', 56);
        $locationService = $repository->getLocationService();
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);
        $newParentLocation = $locationService->loadLocation($rootLocationId);

        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        $movedLocation = $locationService->loadLocation($demoDesignLocationId);

        $this->assertPropertiesCorrect(
            [
                'hidden' => false,
                'invisible' => false,
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => $newParentLocation->pathString . $this->parseId('location', $movedLocation->id) . '/',
            ],
            $movedLocation
        );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testMoveSubtreeThrowsExceptionOnMoveNotIntoContainer(): void
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // Move location from "Home" to "Demo Design" (not container)
        $this->expectException(InvalidArgumentException::class);
        $locationService->moveSubtree($locationToMove, $newParentLocation);
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testMoveSubtreeThrowsExceptionOnMoveToSame(): void
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($mediaLocationId);

        // Load parent location
        $newParentLocation = $locationService->loadLocation($locationToMove->parentLocationId);

        // Move location from "Home" to "Home"
        $this->expectException(InvalidArgumentException::class);
        $locationService->moveSubtree($locationToMove, $newParentLocation);
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeHidden(): void
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);

        // Hide the target location before we move
        $newParentLocation = $locationService->hideLocation($newParentLocation);

        // Move location from "Demo Design" to "Home"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation($demoDesignLocationId);
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            [
                'hidden' => false,
                'invisible' => true,
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => $newParentLocation->pathString . $this->parseId('location', $movedLocation->id) . '/',
            ],
            $movedLocation
        );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeUpdatesSubtreeProperties()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $locationToMove = $locationService->loadLocation($this->generateId('location', 56));
        $newParentLocation = $locationService->loadLocation($this->generateId('location', 43));

        // Load Subtree properties before move
        $expected = $this->loadSubtreeProperties($locationToMove);
        foreach ($expected as $id => $properties) {
            $expected[$id]['depth'] = $properties['depth'] + 2;
            $expected[$id]['pathString'] = str_replace(
                $locationToMove->pathString,
                $newParentLocation->pathString . $this->parseId('location', $locationToMove->id) . '/',
                $properties['pathString']
            );
        }

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);

        // Move location from "Demo Design" to "Home"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation($demoDesignLocationId);
        /* END: Use Case */

        $this->refreshSearch($repository);

        // Load Subtree properties after move
        $actual = $this->loadSubtreeProperties($movedLocation);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtreeUpdatesSubtreeProperties
     */
    public function testMoveSubtreeUpdatesSubtreePropertiesHidden()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $locationToMove = $locationService->loadLocation($this->generateId('location', 2));
        $newParentLocation = $locationService->loadLocation($this->generateId('location', 43));

        // Hide the target location before we move
        $newParentLocation = $locationService->hideLocation($newParentLocation);

        // Load Subtree properties before move
        $expected = $this->loadSubtreeProperties($locationToMove);
        foreach ($expected as $id => $properties) {
            $expected[$id]['invisible'] = true;
            $expected[$id]['depth'] = $properties['depth'] + 1;
            $expected[$id]['pathString'] = str_replace(
                $locationToMove->pathString,
                $newParentLocation->pathString . $this->parseId('location', $locationToMove->id) . '/',
                $properties['pathString']
            );
        }

        $homeLocationId = $this->generateId('location', 2);
        $mediaLocationId = $this->generateId('location', 43);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $homeLocationId is the ID of the "Home" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($homeLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);

        // Move location from "Home" to "Demo Design"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation($homeLocationId);
        /* END: Use Case */

        $this->refreshSearch($repository);

        // Load Subtree properties after move
        $actual = $this->loadSubtreeProperties($movedLocation);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeIncrementsChildCountOfNewParent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $newParentLocation = $locationService->loadLocation($this->generateId('location', 43));

        // Load expected properties before move
        $expected = $this->loadLocationProperties($newParentLocation);
        $childCountBefore = $locationService->getLocationChildCount($newParentLocation);

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);

        // Move location from "Demo Design" to "Home"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation($demoDesignLocationId);

        // Reload new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);
        /* END: Use Case */

        $this->refreshSearch($repository);

        // Load Subtree properties after move
        $actual = $this->loadLocationProperties($newParentLocation);
        $childCountAfter = $locationService->getLocationChildCount($newParentLocation);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($childCountBefore + 1, $childCountAfter);
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeDecrementsChildCountOfOldParent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $oldParentLocation = $locationService->loadLocation($this->generateId('location', 1));

        // Load expected properties before move
        $expected = $this->loadLocationProperties($oldParentLocation);
        $childCountBefore = $locationService->getLocationChildCount($oldParentLocation);

        $homeLocationId = $this->generateId('location', 2);
        $mediaLocationId = $this->generateId('location', 43);
        /* BEGIN: Use Case */
        // $homeLocationId is the ID of the "Home" page location in
        // an eZ Publish demo installation

        // $mediaLocationId is the ID of the "Media" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($mediaLocationId);

        // Get the location id of the old parent
        $oldParentLocationId = $locationToMove->parentLocationId;

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($homeLocationId);

        // Move location from "Demo Design" to "Home"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Reload old parent location
        $oldParentLocation = $locationService->loadLocation($oldParentLocationId);
        /* END: Use Case */

        $this->refreshSearch($repository);

        // Load Subtree properties after move
        $actual = $this->loadLocationProperties($oldParentLocation);
        $childCountAfter = $locationService->getLocationChildCount($oldParentLocation);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($childCountBefore - 1, $childCountAfter);
    }

    /**
     * Test moving invisible (hidden by parent) subtree.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testMoveInvisibleSubtree()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $rootLocationId = 2;

        $folder = $this->createFolder(['eng-GB' => 'Folder'], $rootLocationId);
        $child = $this->createFolder(['eng-GB' => 'Child'], $folder->contentInfo->mainLocationId);
        $locationService->hideLocation(
            $locationService->loadLocation($folder->contentInfo->mainLocationId)
        );
        // sanity check
        $childLocation = $locationService->loadLocation($child->contentInfo->mainLocationId);
        self::assertFalse($childLocation->hidden);
        self::assertTrue($childLocation->invisible);
        self::assertEquals($folder->contentInfo->mainLocationId, $childLocation->parentLocationId);

        $destination = $this->createFolder(['eng-GB' => 'Destination'], $rootLocationId);
        $destinationLocation = $locationService->loadLocation(
            $destination->contentInfo->mainLocationId
        );

        $locationService->moveSubtree($childLocation, $destinationLocation);

        $childLocation = $locationService->loadLocation($child->contentInfo->mainLocationId);
        // Business logic - Location moved to visible parent becomes visible
        self::assertFalse($childLocation->hidden);
        self::assertFalse($childLocation->invisible);
        self::assertEquals($destinationLocation->id, $childLocation->parentLocationId);
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeThrowsInvalidArgumentException(): void
    {
        $repository = $this->getRepository();
        $mediaLocationId = $this->generateId('location', 43);
        $multimediaLocationId = $this->generateId('location', 53);

        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $multimediaLocationId is the ID of the "Multimedia" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($multimediaLocationId);

        // Throws an exception because new parent location is placed below location to move
        $this->expectException(InvalidArgumentException::class);
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );
        /* END: Use Case */
    }

    /**
     * Test that Legacy ezcontentobject_tree.path_identification_string field is correctly updated
     * after moving subtree.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     *
     * @throws \ErrorException
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testMoveSubtreeUpdatesPathIdentificationString(): void
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $topNode = $this->createFolder(['eng-US' => 'top_node'], 2);

        $newParentLocation = $locationService->loadLocation(
            $this
                ->createFolder(['eng-US' => 'Parent'], $topNode->contentInfo->mainLocationId)
                ->contentInfo
                ->mainLocationId
        );
        $location = $locationService->loadLocation(
            $this
                ->createFolder(['eng-US' => 'Move Me'], $topNode->contentInfo->mainLocationId)
                ->contentInfo
                ->mainLocationId
        );

        $locationService->moveSubtree($location, $newParentLocation);

        // path location string is not present on API level, so we need to query database
        $serviceContainer = $this->getSetupFactory()->getServiceContainer();
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $serviceContainer->get('ezpublish.persistence.connection');
        $query = $connection->createQueryBuilder();
        $query
            ->select('path_identification_string')
            ->from('ezcontentobject_tree')
            ->where('node_id = :nodeId')
            ->setParameter('nodeId', $location->id);

        self::assertEquals(
            'top_node/parent/move_me',
            $query->execute()->fetchColumn()
        );
    }

    /**
     * Test that is_visible is set correct for children when moving a content (not the location) which is hidden.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testMoveSubtreeKeepsContentHidden(): void
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load the content service
        $contentService = $repository->getContentService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);

        // Create child below locationToMove
        $subFolderContent = $this->publishContentWithParentLocation('SubFolder', $locationToMove->id);

        // Hide source Content
        $contentService->hideContent($locationToMove->contentInfo);
        $locationToMove = $locationService->loadLocation($demoDesignLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($mediaLocationId);

        // Move location from "Home" to "Media"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation($demoDesignLocationId);
        /* END: Use Case */

        // Assert Moved Location
        $this->assertPropertiesCorrect(
            [
                'hidden' => true, // It should be hidden only on object level, not on location level. But impossible to say due to https://github.com/ezsystems/ezpublish-kernel/blob/7.5/eZ/Publish/Core/Repository/Helper/DomainMapper.php#L562
                'invisible' => true,
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => $newParentLocation->pathString . $this->parseId('location', $movedLocation->id) . '/',
            ],
            $movedLocation
        );

        $this->assertTrue($movedLocation->getContentInfo()->isHidden);

        // Assert child of Moved location
        $childLocation = $locationService->loadLocation($subFolderContent->contentInfo->mainLocationId);
        $this->assertPropertiesCorrect(
            [
                'hidden' => false,
                'invisible' => true,
                'depth' => $movedLocation->depth + 1,
                'parentLocationId' => $movedLocation->id,
                'pathString' => $movedLocation->pathString . $this->parseId('location', $childLocation->id) . '/',
            ],
            $childLocation
        );

        $this->assertFalse($childLocation->getContentInfo()->isHidden);
    }

    /**
     * Loads properties from all locations in the $location's subtree.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param array $properties
     *
     * @return array
     */
    private function loadSubtreeProperties(Location $location, array $properties = [])
    {
        $locationService = $this->getRepository()->getLocationService();

        foreach ($locationService->loadLocationChildren($location)->locations as $childLocation) {
            $properties[] = $this->loadLocationProperties($childLocation);

            $properties = $this->loadSubtreeProperties($childLocation, $properties);
        }

        return $properties;
    }

    /**
     * Loads assertable properties from the given location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param mixed[] $overwrite
     *
     * @return array
     */
    private function loadLocationProperties(Location $location, array $overwrite = [])
    {
        return array_merge(
            [
                'id' => $location->id,
                'depth' => $location->depth,
                'parentLocationId' => $location->parentLocationId,
                'pathString' => $location->pathString,
                'remoteId' => $location->remoteId,
                'hidden' => $location->hidden,
                'invisible' => $location->invisible,
                'priority' => $location->priority,
                'sortField' => $location->sortField,
                'sortOrder' => $location->sortOrder,
            ],
            $overwrite
        );
    }

    /**
     * Assert generated aliases to expected alias return.
     *
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     * @param array $expectedAliases
     */
    protected function assertGeneratedAliases($urlAliasService, array $expectedAliases)
    {
        foreach ($expectedAliases as $expectedAlias) {
            $urlAlias = $urlAliasService->lookup($expectedAlias);
            $this->assertPropertiesCorrect(['type' => 0], $urlAlias);
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     * @param array $expectedSubItemAliases
     */
    private function assertAliasesBeforeCopy($urlAliasService, array $expectedSubItemAliases)
    {
        foreach ($expectedSubItemAliases as $aliasUrl) {
            try {
                $urlAliasService->lookup($aliasUrl);
                $this->fail('We didn\'t expect to find alias, but it was found');
            } catch (\Exception $e) {
                $this->assertTrue(true); // OK - alias was not found
            }
        }
    }

    /**
     * Create and publish Content with the given parent Location.
     *
     * @param string $contentName
     * @param int $parentLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content published Content
     */
    private function publishContentWithParentLocation($contentName, $parentLocationId)
    {
        $repository = $this->getRepository(false);
        $locationService = $repository->getLocationService();

        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-US'
        );
        $contentCreateStruct->setField('name', $contentName);
        $contentDraft = $contentService->createContent(
            $contentCreateStruct,
            [
                $locationService->newLocationCreateStruct($parentLocationId),
            ]
        );

        return $contentService->publishVersion($contentDraft->versionInfo);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function createForumStruct(string $name): ContentCreateStruct
    {
        $repository = $this->getRepository(false);

        $contentTypeForum = $repository->getContentTypeService()
            ->loadContentTypeByIdentifier('forum');

        $forum = $repository->getContentService()
            ->newContentCreateStruct($contentTypeForum, 'eng-GB');

        $forum->setField('name', $name);

        return $forum;
    }

    private function assertAliasExists(
        string $expectedAliasPath,
        Location $location,
        URLAliasServiceInterface $urlAliasService
    ): void {
        $articleAliasesBeforeDelete = $urlAliasService
            ->listLocationAliases($location);

        $this->assertNotEmpty(
            array_filter(
                $articleAliasesBeforeDelete,
                static function (URLAlias $alias) use ($expectedAliasPath) {
                    return $alias->path === $expectedAliasPath;
                }
            )
        );
    }
}
