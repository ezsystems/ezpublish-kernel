<?php

/**
 * File containing a Test Case for LimitationType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\LocationLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as SPIHandler;

/**
 * Test Case for LimitationType.
 */
class LocationLimitationTypeTest extends Base
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $locationHandlerMock;

    /**
     * Setup Location Handler mock.
     */
    public function setUp()
    {
        parent::setUp();
        $this->locationHandlerMock = $this->createMock(SPIHandler::class);
    }

    /**
     * Tear down Location Handler mock.
     */
    public function tearDown()
    {
        unset($this->locationHandlerMock);
        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\Core\Limitation\LocationLimitationType
     */
    public function testConstruct()
    {
        return new LocationLimitationType($this->getPersistenceMock());
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return [
            [new LocationLimitation()],
            [new LocationLimitation([])],
            [new LocationLimitation(['limitationValues' => [0, PHP_INT_MAX, '2', 's3fdaf32r']])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testAcceptValue(LocationLimitation $limitation, LocationLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException()
    {
        return [
            [new ObjectStateLimitation()],
            [new LocationLimitation(['limitationValues' => [true]])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, LocationLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return [
            [new LocationLimitation()],
            [new LocationLimitation([])],
            [new LocationLimitation(['limitationValues' => [2]])],
        ];
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation $limitation
     */
    public function testValidatePass(LocationLimitation $limitation)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('locationHandler')
                ->will($this->returnValue($this->locationHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->locationHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value);
            }
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate($limitation);
        self::assertEmpty($validationErrors);
    }

    /**
     * @return array
     */
    public function providerForTestValidateError()
    {
        return [
            [new LocationLimitation(), 0],
            [new LocationLimitation(['limitationValues' => [0]]), 1],
            [new LocationLimitation(['limitationValues' => [0, PHP_INT_MAX]]), 2],
        ];
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError(LocationLimitation $limitation, $errorCount)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('locationHandler')
                ->will($this->returnValue($this->locationHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->locationHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value)
                    ->will($this->throwException(new NotFoundException('location', $value)));
            }
        } else {
            $this->getPersistenceMock()
                ->expects($this->never())
                ->method($this->anything());
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testBuildValue(LocationLimitationType $limitationType)
    {
        $expected = ['test', 'test' => 9];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(LocationLimitation::class, $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        // Mocks for testing Content & VersionInfo objects, should only be used once because of expect rules.
        $contentMock = $this->createMock(APIContent::class);
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $contentMock
            ->expects($this->once())
            ->method('getVersionInfo')
            ->will($this->returnValue($versionInfoMock));

        $versionInfoMock
            ->expects($this->once())
            ->method('getContentInfo')
            ->will($this->returnValue(new ContentInfo(['published' => true])));

        $versionInfoMock2 = $this->createMock(APIVersionInfo::class);

        $versionInfoMock2
            ->expects($this->once())
            ->method('getContentInfo')
            ->will($this->returnValue(new ContentInfo(['published' => true])));

        return [
            // ContentInfo, with targets, no access
            [
                'limitation' => new LocationLimitation(),
                'object' => new ContentInfo(['published' => true]),
                'targets' => [new Location()],
                'persistence' => [],
                'expected' => false,
            ],
            // ContentInfo, with targets, no access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['published' => true]),
                'targets' => [new Location(['id' => 55])],
                'persistence' => [],
                'expected' => false,
            ],
            // ContentInfo, with targets, with access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['published' => true]),
                'targets' => [new Location(['id' => 2])],
                'persistence' => [],
                'expected' => true,
            ],
            // ContentInfo, no targets, with access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['published' => true]),
                'targets' => null,
                'persistence' => [new Location(['id' => 2])],
                'expected' => true,
            ],
            // ContentInfo, no targets, no access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2, 43]]),
                'object' => new ContentInfo(['published' => true]),
                'targets' => null,
                'persistence' => [new Location(['id' => 55])],
                'expected' => false,
            ],
            // ContentInfo, no targets, un-published, with access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['published' => false]),
                'targets' => null,
                'persistence' => [new Location(['id' => 2])],
                'expected' => true,
            ],
            // ContentInfo, no targets, un-published, no access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2, 43]]),
                'object' => new ContentInfo(['published' => false]),
                'targets' => null,
                'persistence' => [new Location(['id' => 55])],
                'expected' => false,
            ],
            // Content, with targets, with access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => $contentMock,
                'targets' => [new Location(['id' => 2])],
                'persistence' => [],
                'expected' => true,
            ],
            // VersionInfo, with targets, with access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => $versionInfoMock2,
                'targets' => [new Location(['id' => 2])],
                'persistence' => [],
                'expected' => true,
            ],
            // ContentCreateStruct, no targets, no access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2]]),
                'object' => new ContentCreateStruct(),
                'targets' => [],
                'persistence' => [],
                'expected' => false,
            ],
            // ContentCreateStruct, with targets, no access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2, 43]]),
                'object' => new ContentCreateStruct(),
                'targets' => [new LocationCreateStruct(['parentLocationId' => 55])],
                'persistence' => [],
                'expected' => false,
            ],
            // ContentCreateStruct, with targets, with access
            [
                'limitation' => new LocationLimitation(['limitationValues' => [2, 43]]),
                'object' => new ContentCreateStruct(),
                'targets' => [new LocationCreateStruct(['parentLocationId' => 43])],
                'persistence' => [],
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        LocationLimitation $limitation,
        ValueObject $object,
        $targets,
        array $persistenceLocations,
        $expected
    ) {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $persistenceMock = $this->getPersistenceMock();
        if (empty($persistenceLocations) && $targets !== null) {
            $persistenceMock
                ->expects($this->never())
                ->method($this->anything());
        } else {
            $this->getPersistenceMock()
                ->expects($this->once())
                ->method('locationHandler')
                ->will($this->returnValue($this->locationHandlerMock));

            $this->locationHandlerMock
                ->expects($this->once())
                ->method($object instanceof ContentInfo && $object->published ? 'loadLocationsByContent' : 'loadParentLocationsForDraftContent')
                ->with($object->id)
                ->will($this->returnValue($persistenceLocations));
        }

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );

        self::assertInternalType('boolean', $value);
        self::assertEquals($expected, $value);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument()
    {
        return [
            // invalid limitation
            [
                'limitation' => new ObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => [new Location()],
                'persistence' => [],
            ],
            // invalid object
            [
                'limitation' => new LocationLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => [new Location()],
                'persistence' => [],
            ],
            // invalid target
            [
                'limitation' => new LocationLimitation(),
                'object' => new ContentInfo(),
                'targets' => [new ObjectStateLimitation()],
                'persistence' => [],
            ],
            // invalid target when using ContentCreateStruct
            [
                'limitation' => new LocationLimitation(),
                'object' => new ContentCreateStruct(),
                'targets' => [new Location()],
                'persistence' => [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        $targets,
        array $persistenceLocations
    ) {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $persistenceMock = $this->getPersistenceMock();
        $persistenceMock
            ->expects($this->never())
            ->method($this->anything());

        $v = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );
        var_dump($v); // intentional, debug in case no exception above
    }

    /**
     * @depends testConstruct
     * @expectedException \RuntimeException
     *
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue(LocationLimitationType $limitationType)
    {
        $limitationType->getCriterion(
            new LocationLimitation([]),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testGetCriterionSingleValue(LocationLimitationType $limitationType)
    {
        $criterion = $limitationType->getCriterion(
            new LocationLimitation(['limitationValues' => [9]]),
            $this->getUserMock()
        );

        self::assertInstanceOf(LocationId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::EQ, $criterion->operator);
        self::assertEquals([9], $criterion->value);
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testGetCriterionMultipleValues(LocationLimitationType $limitationType)
    {
        $criterion = $limitationType->getCriterion(
            new LocationLimitation(['limitationValues' => [9, 55]]),
            $this->getUserMock()
        );

        self::assertInstanceOf(LocationId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::IN, $criterion->operator);
        self::assertEquals([9, 55], $criterion->value);
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\LocationLimitationType $limitationType
     */
    public function testValueSchema(LocationLimitationType $limitationType)
    {
        self::assertEquals(
            LocationLimitationType::VALUE_SCHEMA_LOCATION_ID,
            $limitationType->valueSchema()
        );
    }
}
