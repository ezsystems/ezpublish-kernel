<?php

/**
 * File containing a Test Case for LimitationType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\NewObjectStateLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as SPIHandler;

/**
 * Test Case for LimitationType.
 */
class NewObjectStateLimitationTypeTest extends Base
{
    /** @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $objectStateHandlerMock;

    /**
     * Setup Handler mock.
     */
    public function setUp()
    {
        parent::setUp();
        $this->objectStateHandlerMock = $this->createMock(SPIHandler::class);
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
     * @return \eZ\Publish\Core\Limitation\NewObjectStateLimitationType
     */
    public function testConstruct()
    {
        return new NewObjectStateLimitationType($this->getPersistenceMock());
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return [
            [new NewObjectStateLimitation()],
            [new NewObjectStateLimitation([])],
            [new NewObjectStateLimitation(['limitationValues' => [0, PHP_INT_MAX, '2', 's3fdaf32r']])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testAcceptValue(NewObjectStateLimitation $limitation, NewObjectStateLimitationType $limitationType)
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
            [new NewObjectStateLimitation(['limitationValues' => [true]])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, NewObjectStateLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return [
            [new NewObjectStateLimitation()],
            [new NewObjectStateLimitation([])],
            [new NewObjectStateLimitation(['limitationValues' => [2]])],
        ];
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation $limitation
     */
    public function testValidatePass(NewObjectStateLimitation $limitation)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('objectStateHandler')
                ->will($this->returnValue($this->objectStateHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->objectStateHandlerMock
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
            [new NewObjectStateLimitation(), 0],
            [new NewObjectStateLimitation(['limitationValues' => [0]]), 1],
            [new NewObjectStateLimitation(['limitationValues' => [0, PHP_INT_MAX]]), 2],
        ];
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError(NewObjectStateLimitation $limitation, $errorCount)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('objectStateHandler')
                ->will($this->returnValue($this->objectStateHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->objectStateHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value)
                    ->will($this->throwException(new NotFoundException('contentType', $value)));
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
     * @param \eZ\Publish\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testBuildValue(NewObjectStateLimitationType $limitationType)
    {
        $expected = ['test', 'test' => 9];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf(NewObjectStateLimitation::class, $value);
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
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => false,
            ],
            // Content, no access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2]]),
                'object' => new Content(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => false,
            ],
            // Content, no access  (both must match!)
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2, 22]]),
                'object' => new Content(),
                'targets' => [new ObjectState(['id' => 2]), new ObjectState(['id' => 66])],
                'expected' => false,
            ],
            // ContentInfo, with access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [66]]),
                'object' => new ContentInfo(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => true,
            ],
            // VersionInfo, with access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2, 66]]),
                'object' => new VersionInfo(),
                'targets' => [new ObjectState(['id' => 66])],
                'expected' => true,
            ],
            // Content, with access
            [
                'limitation' => new NewObjectStateLimitation(['limitationValues' => [2, 66]]),
                'object' => new Content(),
                'targets' => [new ObjectState(['id' => 66]), new ObjectState(['id' => 2])],
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        NewObjectStateLimitation $limitation,
        ValueObject $object,
        array $targets,
        $expected
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
            ],
            // invalid object
            [
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => [new Location()],
            ],
            // empty targets
            [
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => [],
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testGetCriterion(NewObjectStateLimitationType $limitationType)
    {
        $limitationType->getCriterion(
            new NewObjectStateLimitation([]),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\NewObjectStateLimitationType $limitationType
     */
    public function testValueSchema(NewObjectStateLimitationType $limitationType)
    {
        self::assertEquals(
            [],
            $limitationType->valueSchema()
        );
    }
}
