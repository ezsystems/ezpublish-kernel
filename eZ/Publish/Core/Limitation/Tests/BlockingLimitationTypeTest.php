<?php

/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchNone;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Limitation\BlockingLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;

/**
 * Test Case for LimitationType.
 */
class BlockingLimitationTypeTest extends Base
{
    /**
     * @return \eZ\Publish\Core\Limitation\BlockingLimitationType
     */
    public function testConstruct()
    {
        return new BlockingLimitationType('Test');
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return [
            [new BlockingLimitation('Test', [])],
            [new BlockingLimitation('FunctionList', [])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testAcceptValue(BlockingLimitation $limitation, BlockingLimitationType $limitationType)
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
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, BlockingLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return [
            [new BlockingLimitation('Test', ['limitationValues' => ['ezjscore::call']])],
            [new BlockingLimitation('Test', ['limitationValues' => ['ezjscore::call', 'my::call']])],
        ];
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation $limitation
     */
    public function testValidatePass(BlockingLimitation $limitation)
    {
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
            [new BlockingLimitation('Test', []), 1],
            [new BlockingLimitation('Test', ['limitationValues' => [0]]), 0],
            [new BlockingLimitation('Test', ['limitationValues' => [0, PHP_INT_MAX]]), 0],
        ];
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError(BlockingLimitation $limitation, $errorCount)
    {
        $this->getPersistenceMock()
                ->expects($this->never())
                ->method($this->anything());

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testBuildValue(BlockingLimitationType $limitationType)
    {
        $expected = ['test', 'test' => 9];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(BlockingLimitation::class, $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return [
            // ContentInfo, no access
            [
                'limitation' => new BlockingLimitation('Test', []),
                'object' => new ContentInfo(),
                'targets' => [],
            ],
            // ContentInfo, no access
            [
                'limitation' => new BlockingLimitation('Test', ['limitationValues' => [2]]),
                'object' => new ContentInfo(),
                'targets' => [],
            ],
            // ContentInfo, with access
            [
                'limitation' => new BlockingLimitation('Test', ['limitationValues' => [66]]),
                'object' => new ContentInfo(['contentTypeId' => 66]),
                'targets' => [],
            ],
            // ContentCreateStruct, no access
            [
                'limitation' => new BlockingLimitation('Test', ['limitationValues' => [2]]),
                'object' => new ContentCreateStruct(['contentType' => ((object)['id' => 22])]),
                'targets' => [],
            ],
            // ContentCreateStruct, with access
            [
                'limitation' => new BlockingLimitation('Test', ['limitationValues' => [2, 43]]),
                'object' => new ContentCreateStruct(['contentType' => ((object)['id' => 43])]),
                'targets' => [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        BlockingLimitation $limitation,
        ValueObject $object,
        array $targets
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

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );

        self::assertFalse($value);
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
        array $targets
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
     *
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testGetCriterion(BlockingLimitationType $limitationType)
    {
        $criterion = $limitationType->getCriterion(
            new BlockingLimitation('Test', []),
            $this->getUserMock()
        );

        self::assertInstanceOf(MatchNone::class, $criterion);
        self::assertInternalType('null', $criterion->value);
        self::assertInternalType('null', $criterion->operator);
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testValueSchema(BlockingLimitationType $limitationType)
    {
        self::assertEquals(
            [],
            $limitationType->valueSchema()
        );
    }
}
