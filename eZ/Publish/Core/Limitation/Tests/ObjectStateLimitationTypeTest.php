<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ObjectStateId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Limitation\ObjectStateLimitationType;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as SPIHandler;

/**
 * Test Case for LimitationType.
 */
class ObjectStateLimitationTypeTest extends Base
{
    /** @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $objectStateHandlerMock;

    /** @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Group[] */
    private $allObjectStateGroups;

    /** @var array */
    private $loadObjectStatesMap;

    /**
     * Setup Handler mock.
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectStateHandlerMock = $this->createMock(SPIHandler::class);

        $this->allObjectStateGroups = [
            new Group(['id' => 1]),
            new Group(['id' => 2]),
        ];

        $this->loadObjectStatesMap = [
            [
                1,
                [
                    new ObjectState(['id' => 1, 'priority' => 1]),
                    new ObjectState(['id' => 2, 'priority' => 0]),
                ],
            ],
            [
                2,
                [
                    new ObjectState(['id' => 3, 'priority' => 1]),
                    new ObjectState(['id' => 4, 'priority' => 0]),
                ],
            ],
        ];
    }

    /**
     * Tear down Handler mock.
     */
    public function tearDown()
    {
        unset($this->objectStateHandlerMock);

        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\Core\Limitation\ObjectStateLimitationType
     */
    public function testConstruct()
    {
        return new ObjectStateLimitationType($this->getPersistenceMock());
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate(): array
    {
        return [
            'ContentInfo, published, no Limitations, no access' => [
                'limitation' => new ObjectStateLimitation(),
                'object' => new ContentInfo(['id' => 1, 'published' => true]),
                'expected' => false,
            ],
            'ContentInfo, published, with Limitation=2, no access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['id' => 1, 'published' => true]),
                'expected' => false,
            ],
            'ContentInfo, published, with Limitations=(2, 3), no access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [2, 3]]),
                'object' => new ContentInfo(['id' => 1, 'published' => true]),
                'expected' => false,
            ],
            'ContentInfo, published, with Limitations=(2, 4), no access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [2, 4]]),
                'object' => new ContentInfo(['id' => 1, 'published' => true]),
                'expected' => false,
            ],
            'ContentInfo, published, with Limitation=1, with access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [1]]),
                'object' => new ContentInfo(['id' => 1, 'published' => true]),
                'expected' => true,
            ],
            'ContentInfo, published, with Limitations=(1, 3), with access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [1, 3]]),
                'object' => new ContentInfo(['id' => 1, 'published' => true]),
                'expected' => true,
            ],
            'ContentInfo, not published, with Limitation=2, no access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['id' => 1, 'published' => false]),
                'expected' => false,
            ],
            'ContentInfo, not published, with Limitation=(1, 3), with access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [1, 3]]),
                'object' => new ContentInfo(['id' => 1, 'published' => false]),
                'expected' => true,
            ],
            'RootLocation, no object states assigned' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [1, 3]]),
                'object' => new ContentInfo(['id' => 0, 'mainLocationId' => 1, 'published' => true]),
                'expected' => true,
            ],
            'Non-RootLocation, published, with Limitation=2' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [2]]),
                'object' => new ContentInfo(['id' => 1, 'mainLocationId' => 2, 'published' => true]),
                'expected' => false,
            ],
            'ContentCreateStruct, with access' => [
                'limitation' => new ObjectStateLimitation(['limitationValues' => [1, 3]]),
                'object' => new ContentCreateStruct(),
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        ObjectStateLimitation $limitation,
        ValueObject $object,
        $expected
    ) {
        $getContentStateMap = [
            [
                1,
                1,
                new ObjectState(['id' => 1]),
            ],
            [
                1,
                2,
                new ObjectState(['id' => 3]),
            ],
        ];

        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                 ->method('objectStateHandler')
                 ->willReturn($this->objectStateHandlerMock);

            $this->objectStateHandlerMock
                ->method('loadAllGroups')
                ->willReturn($this->allObjectStateGroups);

            $this->objectStateHandlerMock
                ->method('loadObjectStates')
                ->willReturnMap($this->loadObjectStatesMap);

            $this->objectStateHandlerMock
                ->method('getContentState')
                ->willReturnMap($getContentStateMap);
        } else {
            $this->getPersistenceMock()
                 ->expects($this->never())
                 ->method($this->anything());
        }

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
        );

        self::assertSame($expected, $value);
    }

    /**
     * @depends testConstruct
     * @expectedException \RuntimeException
     *
     * @param \eZ\Publish\Core\Limitation\ObjectStateLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue(ObjectStateLimitationType $limitationType)
    {
        $limitationType->getCriterion(
            new ObjectStateLimitation([]),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\ObjectStateLimitationType $limitationType
     */
    public function testGetCriterionSingleValue(ObjectStateLimitationType $limitationType)
    {
        $criterion = $limitationType->getCriterion(
            new ObjectStateLimitation(['limitationValues' => [2]]),
            $this->getUserMock()
        );

        self::assertInstanceOf(ObjectStateId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::EQ, $criterion->operator);
        self::assertEquals([2], $criterion->value);
    }

    public function testGetCriterionMultipleValuesFromSingleGroup()
    {
        $this->getPersistenceMock()
             ->method('objectStateHandler')
             ->willReturn($this->objectStateHandlerMock);

        $this->objectStateHandlerMock
            ->method('loadAllGroups')
            ->willReturn($this->allObjectStateGroups);

        $this->objectStateHandlerMock
            ->method('loadObjectStates')
            ->willReturnMap($this->loadObjectStatesMap);

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $criterion = $limitationType->getCriterion(
            new ObjectStateLimitation(['limitationValues' => [1, 2]]),
            $this->getUserMock()
        );

        self::assertInstanceOf(ObjectStateId::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::IN, $criterion->operator);
        self::assertEquals([1, 2], $criterion->value);
    }

    public function testGetCriterionMultipleValuesFromMultipleGroups()
    {
        $this->getPersistenceMock()
             ->method('objectStateHandler')
             ->willReturn($this->objectStateHandlerMock);

        $this->objectStateHandlerMock
            ->method('loadAllGroups')
            ->willReturn($this->allObjectStateGroups);

        $this->objectStateHandlerMock
            ->method('loadObjectStates')
            ->willReturnMap($this->loadObjectStatesMap);

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $criterion = $limitationType->getCriterion(
            new ObjectStateLimitation(['limitationValues' => [1, 2, 3]]),
            $this->getUserMock()
        );

        self::assertInstanceOf(LogicalAnd::class, $criterion);
        self::assertInternalType('array', $criterion->criteria);

        self::assertInternalType('array', $criterion->criteria[0]->value);
        self::assertInternalType('string', $criterion->criteria[0]->operator);
        self::assertEquals(Operator::IN, $criterion->criteria[0]->operator);
        self::assertEquals([1, 2], $criterion->criteria[0]->value);

        self::assertInternalType('array', $criterion->criteria[1]->value);
        self::assertInternalType('string', $criterion->criteria[1]->operator);
        self::assertEquals(Operator::IN, $criterion->criteria[1]->operator);
        self::assertEquals([3], $criterion->criteria[1]->value);
    }
}
