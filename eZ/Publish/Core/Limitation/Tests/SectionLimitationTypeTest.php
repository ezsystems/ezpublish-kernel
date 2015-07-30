<?php

/**
 * This file is part of the eZ Publish unit tests package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\SectionLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Section as SPISection;
use eZ\Publish\SPI\Limitation\Type as LimitationType;

/**
 * Test Case for LimitationType.
 */
class SectionLimitationTypeTest extends Base
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sectionHandlerMock;

    /**
     * Setup Location Handler mock.
     */
    public function setUp()
    {
        parent::setUp();

        $this->sectionHandlerMock = $this->getMock(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler',
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * Tear down Location Handler mock.
     */
    public function tearDown()
    {
        unset($this->sectionHandlerMock);
        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\Core\Limitation\SectionLimitationType
     */
    public function testConstruct()
    {
        return new SectionLimitationType($this->getPersistenceMock());
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array(new SectionLimitation()),
            array(new SectionLimitation(array())),
            array(new SectionLimitation(array('limitationValues' => array('', 'true', '2', 's3fdaf32r')))),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testAcceptValue(SectionLimitation $limitation, SectionLimitationType $limitationType)
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
            array(new SectionLimitation(array('limitationValues' => array(true)))),
            array(new SectionLimitation(array('limitationValues' => array(new \stdClass())))),
            array(new SectionLimitation(array('limitationValues' => array(null)))),
            array(new SectionLimitation(array('limitationValues' => '/1/2/'))),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, SectionLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return array(
            array(new SectionLimitation()),
            array(new SectionLimitation(array())),
            array(new SectionLimitation(array('limitationValues' => array('1')))),
        );
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation $limitation
     */
    public function testValidatePass(SectionLimitation $limitation)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('sectionHandler')
                ->will($this->returnValue($this->sectionHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->sectionHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value)
                    ->will(
                        $this->returnValue(
                            new SPISection(array('id' => $value))
                        )
                    );
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
            array(new SectionLimitation(), 0),
            array(new SectionLimitation(array('limitationValues' => array('777'))), 1),
            array(new SectionLimitation(array('limitationValues' => array('888', '999'))), 2),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError(SectionLimitation $limitation, $errorCount)
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->expects($this->any())
                ->method('sectionHandler')
                ->will($this->returnValue($this->sectionHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->sectionHandlerMock
                    ->expects($this->at($key))
                    ->method('load')
                    ->with($value)
                    ->will($this->throwException(new NotFoundException('Section', $value)));
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
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testBuildValue(SectionLimitationType $limitationType)
    {
        $expected = array('test', 'test' => '33');
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf('\eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation', $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        // Mocks for testing Content & VersionInfo objects, should only be used once because of expect rules.
        $contentMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            array(),
            array(),
            '',
            false
        );

        $versionInfoMock = $this->getMock(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            array(),
            array(),
            '',
            false
        );

        $contentMock
            ->expects($this->once())
            ->method('getVersionInfo')
            ->will($this->returnValue($versionInfoMock));

        $versionInfoMock
            ->expects($this->once())
            ->method('getContentInfo')
            ->will($this->returnValue(new ContentInfo(array('sectionId' => 2))));

        $versionInfoMock2 = $this->getMock(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            array(),
            array(),
            '',
            false
        );

        $versionInfoMock2
            ->expects($this->once())
            ->method('getContentInfo')
            ->will($this->returnValue(new ContentInfo(array('sectionId' => 2))));

        return array(
            // ContentInfo, with targets, no access
            array(
                'limitation' => new SectionLimitation(),
                'object' => new ContentInfo(array('sectionId' => 55)),
                'targets' => array(new Location()),
                'expected' => LimitationType::ACCESS_DENIED,
            ),
            // ContentInfo, with targets, no access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => new ContentInfo(array('sectionId' => 55)),
                'targets' => array(new Location(array('pathString' => '/1/55'))),
                'expected' => LimitationType::ACCESS_DENIED,
            ),
            // ContentInfo, with targets, with access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => new ContentInfo(array('sectionId' => 2)),
                'targets' => array(new Location(array('pathString' => '/1/2/'))),
                'expected' => LimitationType::ACCESS_GRANTED,
            ),
            // ContentInfo, no targets, with access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => new ContentInfo(array('sectionId' => 2)),
                'targets' => null,
                'expected' => LimitationType::ACCESS_GRANTED,
            ),
            // ContentInfo, no targets, no access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2', '43'))),
                'object' => new ContentInfo(array('sectionId' => 55)),
                'targets' => null,
                'expected' => LimitationType::ACCESS_DENIED,
            ),
            // ContentInfo, no targets, un-published, with access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => new ContentInfo(array('published' => false, 'sectionId' => 2)),
                'targets' => null,
                'expected' => LimitationType::ACCESS_GRANTED,
            ),
            // ContentInfo, no targets, un-published, no access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2', '43'))),
                'object' => new ContentInfo(array('published' => false, 'sectionId' => 55)),
                'targets' => null,
                'expected' => LimitationType::ACCESS_DENIED,
            ),
            // Content, with targets, with access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => $contentMock,
                'targets' => array(new Location(array('pathString' => '/1/2/'))),
                'expected' => LimitationType::ACCESS_GRANTED,
            ),
            // VersionInfo, with targets, with access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => $versionInfoMock2,
                'targets' => array(new Location(array('pathString' => '/1/2/'))),
                'expected' => LimitationType::ACCESS_GRANTED,
            ),
            // ContentCreateStruct, no targets, no access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2'))),
                'object' => new ContentCreateStruct(),
                'targets' => array(),
                'expected' => LimitationType::ACCESS_DENIED,
            ),
            // ContentCreateStruct, with targets, no access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2', '43'))),
                'object' => new ContentCreateStruct(array('sectionId' => 55)),
                'targets' => array(new LocationCreateStruct(array('parentLocationId' => 55))),
                'expected' => LimitationType::ACCESS_DENIED,
            ),
            // ContentCreateStruct, with targets, with access
            array(
                'limitation' => new SectionLimitation(array('limitationValues' => array('2', '43'))),
                'object' => new ContentCreateStruct(array('sectionId' => 43)),
                'targets' => array(new LocationCreateStruct(array('parentLocationId' => 55))),
                'expected' => LimitationType::ACCESS_GRANTED,
            ),
            // invalid object
            array(
                'limitation' => new SectionLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => array(new LocationCreateStruct(array('parentLocationId' => 43))),
                'expected' => LimitationType::ACCESS_ABSTAIN,
            ),
            // invalid target
            array(
                'limitation' => new SectionLimitation(),
                'object' => new ContentInfo(array('published' => true)),
                'targets' => array(new ObjectStateLimitation()),
                'expected' => LimitationType::ACCESS_ABSTAIN,
            ),
        );
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        SectionLimitation $limitation,
        ValueObject $object,
        $targets,
        $expected
    ) {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $this->getPersistenceMock()
            ->expects($this->never())
            ->method($this->anything());

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );

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
        );
    }

    /**
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        $targets
    ) {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $this->getPersistenceMock()
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
     * @expectedException \RuntimeException
     *
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue(SectionLimitationType $limitationType)
    {
        $limitationType->getCriterion(
            new SectionLimitation(array()),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testGetCriterionSingleValue(SectionLimitationType $limitationType)
    {
        $criterion = $limitationType->getCriterion(
            new SectionLimitation(array('limitationValues' => array('9'))),
            $this->getUserMock()
        );

        self::assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId', $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::EQ, $criterion->operator);
        self::assertEquals(array('9'), $criterion->value);
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testGetCriterionMultipleValues(SectionLimitationType $limitationType)
    {
        $criterion = $limitationType->getCriterion(
            new SectionLimitation(array('limitationValues' => array('9', '55'))),
            $this->getUserMock()
        );

        self::assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId', $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::IN, $criterion->operator);
        self::assertEquals(array('9', '55'), $criterion->value);
    }

    /**
     * @depends testConstruct
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\SectionLimitationType $limitationType
     */
    public function testValueSchema(SectionLimitationType $limitationType)
    {
        $limitationType->valueSchema();
    }
}
