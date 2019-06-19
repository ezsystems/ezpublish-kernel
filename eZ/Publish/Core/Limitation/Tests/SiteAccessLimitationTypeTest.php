<?php

/**
 * File containing a Test Case for LimitationType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Limitation\SiteAccessLimitationType;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Test Case for LimitationType.
 */
class SiteAccessLimitationTypeTest extends Base
{
    private $siteAccessList = ['ezdemo_site', 'eng', 'fre'];

    /**
     * @return \eZ\Publish\Core\Limitation\SiteAccessLimitationType
     */
    public function testConstruct()
    {
        return new SiteAccessLimitationType($this->siteAccessList);
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return [
            [new SiteAccessLimitation()],
            [new SiteAccessLimitation([])],
            [
                new SiteAccessLimitation(
                    [
                        'limitationValues' => [
                            sprintf('%u', crc32('ezdemo_site')),
                            sprintf('%u', crc32('eng')),
                            sprintf('%u', crc32('fre')),
                        ],
                    ]
                ),
            ],
            [
                new SiteAccessLimitation(
                    [
                        'limitationValues' => [
                            crc32('ezdemo_site'),
                            crc32('eng'),
                            crc32('fre'),
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValue
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testAcceptValue(SiteAccessLimitation $limitation, SiteAccessLimitationType $limitationType)
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
            [new SiteAccessLimitation(['limitationValues' => [true]])],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValueException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testAcceptValueException(Limitation $limitation, SiteAccessLimitationType $limitationType)
    {
        $limitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidateError()
    {
        return [
            [new SiteAccessLimitation(), 0],
            [new SiteAccessLimitation([]), 0],
            [
                new SiteAccessLimitation(
                    [
                        'limitationValues' => ['2339567439'],
                    ]
                ),
                1,
            ],
            [new SiteAccessLimitation(['limitationValues' => [true]]), 1],
            [
                new SiteAccessLimitation(
                    [
                        'limitationValues' => [
                            '2339567439',
                            false,
                        ],
                    ]
                ),
                2,
            ],
            [
                new SiteAccessLimitation(
                    [
                        'limitationValues' => [
                            sprintf('%u', crc32('ezdemo_site')),
                            sprintf('%u', crc32('eng')),
                            sprintf('%u', crc32('fre')),
                        ],
                    ]
                ),
                0,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestValidateError
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation $limitation
     * @param int $errorCount
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testValidateError(SiteAccessLimitation $limitation, $errorCount, SiteAccessLimitationType $limitationType)
    {
        $validationErrors = $limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testBuildValue(SiteAccessLimitationType $limitationType)
    {
        $expected = ['test', 'test' => 9];
        $value = $limitationType->buildValue($expected);

        self::assertInstanceOf('\eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation', $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return [
            // SiteAccess, no access
            [
                'limitation' => new SiteAccessLimitation(),
                'object' => new SiteAccess('behat_site'),
                'expected' => false,
            ],
            // SiteAccess, no access
            [
                'limitation' => new SiteAccessLimitation(['limitationValues' => ['2339567439']]),
                'object' => new SiteAccess('behat_site'),
                'expected' => false,
            ],
            // SiteAccess, with access
            [
                'limitation' => new SiteAccessLimitation(['limitationValues' => ['1817462202']]),
                'object' => new SiteAccess('behat_site'),
                'expected' => true,
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        SiteAccessLimitation $limitation,
        ValueObject $object,
        $expected,
        SiteAccessLimitationType $limitationType
    ) {
        $userMock = $this->getUserMock();
        $userMock->expects($this->never())->method($this->anything());

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
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
                'object' => new SiteAccess(),
            ],
            // invalid object
            [
                'limitation' => new SiteAccessLimitation(),
                'object' => new ObjectStateLimitation(),
            ],
        ];
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        SiteAccessLimitationType $limitationType
    ) {
        $userMock = $this->getUserMock();
        $userMock->expects($this->never())->method($this->anything());

        $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
        );
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testGetCriterion(SiteAccessLimitationType $limitationType)
    {
        $limitationType->getCriterion(new SiteAccessLimitation(), $this->getUserMock());
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SiteAccessLimitationType $limitationType
     */
    public function testValueSchema(SiteAccessLimitationType $limitationType)
    {
        self::markTestSkipped('Method valueSchema() is not implemented');
    }
}
