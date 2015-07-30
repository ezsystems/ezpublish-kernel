<?php

/**
 * File containing a Test Case for LimitationType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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

/**
 * Test Case for LimitationType.
 */
class NewObjectStateLimitationTypeTest extends Base
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectStateHandlerMock;

    /**
     * Setup Handler mock.
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectStateHandlerMock = $this->getMock(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler',
            array(),
            array(),
            '',
            false
        );
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
        return array(
            array(new NewObjectStateLimitation()),
            array(new NewObjectStateLimitation(array())),
            array(new NewObjectStateLimitation(array('limitationValues' => array(0, PHP_INT_MAX, '2', 's3fdaf32r')))),
        );
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
        return array(
            array(new ObjectStateLimitation()),
            array(new NewObjectStateLimitation(array('limitationValues' => array(true)))),
        );
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
        return array(
            array(new NewObjectStateLimitation()),
            array(new NewObjectStateLimitation(array())),
            array(new NewObjectStateLimitation(array('limitationValues' => array(2)))),
        );
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
        return array(
            array(new NewObjectStateLimitation(), 0),
            array(new NewObjectStateLimitation(array('limitationValues' => array(0))), 1),
            array(new NewObjectStateLimitation(array('limitationValues' => array(0, PHP_INT_MAX))), 2),
        );
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
        $expected = array('test', 'test' => 9);
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf('\eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation', $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return array(
            // ContentInfo, no access
            array(
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => array(new ObjectState(array('id' => 66))),
                'expected' => false,
            ),
            // Content, no access
            array(
                'limitation' => new NewObjectStateLimitation(array('limitationValues' => array(2))),
                'object' => new Content(),
                'targets' => array(new ObjectState(array('id' => 66))),
                'expected' => false,
            ),
            // Content, no access  (both must match!)
            array(
                'limitation' => new NewObjectStateLimitation(array('limitationValues' => array(2, 22))),
                'object' => new Content(),
                'targets' => array(new ObjectState(array('id' => 2)), new ObjectState(array('id' => 66))),
                'expected' => false,
            ),
            // ContentInfo, with access
            array(
                'limitation' => new NewObjectStateLimitation(array('limitationValues' => array(66))),
                'object' => new ContentInfo(),
                'targets' => array(new ObjectState(array('id' => 66))),
                'expected' => true,
            ),
            // VersionInfo, with access
            array(
                'limitation' => new NewObjectStateLimitation(array('limitationValues' => array(2, 66))),
                'object' => new VersionInfo(),
                'targets' => array(new ObjectState(array('id' => 66))),
                'expected' => true,
            ),
            // Content, with access
            array(
                'limitation' => new NewObjectStateLimitation(array('limitationValues' => array(2, 66))),
                'object' => new Content(),
                'targets' => array(new ObjectState(array('id' => 66)), new ObjectState(array('id' => 2))),
                'expected' => true,
            ),
        );
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
        return array(
            // invalid limitation
            array(
                'limitation' => new ObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => array(new Location()),
            ),
            // invalid object
            array(
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => array(new Location()),
            ),
            // empty targets
            array(
                'limitation' => new NewObjectStateLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => array(),
            ),
        );
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
        var_dump($v);// intentional, debug in case no exception above
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
            new NewObjectStateLimitation(array()),
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
            array(),
            $limitationType->valueSchema()
        );
    }
}
