<?php
/**
 * File containing a Test Case for LimitationType class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Limitation\StatusLimitationType;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;

/**
 * Test Case for LimitationType
 */
class StatusLimitationTypeTest extends Base
{
    /**
     * @return \eZ\Publish\Core\Limitation\StatusLimitationType
     */
    public function testConstruct()
    {
        return new StatusLimitationType();
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array( new StatusLimitation() ),
            array( new StatusLimitation( array() ) ),
            array(
                new StatusLimitation(
                    array(
                        'limitationValues' => array(
                            VersionInfo::STATUS_DRAFT,
                            VersionInfo::STATUS_PUBLISHED,
                            VersionInfo::STATUS_ARCHIVED
                        )
                    )
                )
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValue
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\StatusLimitationType $limitationType
     */
    public function testAcceptValue( StatusLimitation $limitation, StatusLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException()
    {
        return array(
            array( new ObjectStateLimitation() ),
            array( new StatusLimitation( array( 'limitationValues' => array( true ) ) ) ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestAcceptValueException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\StatusLimitationType $limitationType
     */
    public function testAcceptValueException( Limitation $limitation, StatusLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestValidateError()
    {
        return array(
            array( new StatusLimitation(), 0 ),
            array( new StatusLimitation( array() ), 0 ),
            array(
                new StatusLimitation(
                    array(
                        'limitationValues' => array( SPIVersionInfo::STATUS_PUBLISHED )
                    )
                ),
                0
            ),
            array( new StatusLimitation( array( 'limitationValues' => array( 100 ) ) ), 1 ),
            array(
                new StatusLimitation(
                    array(
                        'limitationValues' => array(
                            SPIVersionInfo::STATUS_PUBLISHED,
                            PHP_INT_MAX
                        )
                    )
                ),
                1
            ),
            array(
                new StatusLimitation(
                    array(
                        'limitationValues' => array(
                            SPIVersionInfo::STATUS_PENDING,
                            SPIVersionInfo::STATUS_REJECTED
                        )
                    )
                ),
                2
            ),
            array(
                new StatusLimitation(
                    array(
                        'limitationValues' => array(
                            SPIVersionInfo::STATUS_DRAFT,
                            SPIVersionInfo::STATUS_PUBLISHED,
                            SPIVersionInfo::STATUS_ARCHIVED
                        )
                    )
                ),
                0
            ),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation $limitation
     * @param int $errorCount
     * @param \eZ\Publish\Core\Limitation\StatusLimitationType $limitationType
     */
    public function testValidateError( StatusLimitation $limitation, $errorCount, StatusLimitationType $limitationType )
    {
        $validationErrors = $limitationType->validate( $limitation );
        self::assertCount( $errorCount, $validationErrors );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\StatusLimitationType $limitationType
     */
    public function testBuildValue( StatusLimitationType $limitationType )
    {
        $expected = array( 'test', 'test' => 9 );
        $value = $limitationType->buildValue( $expected );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation', $value );
        self::assertInternalType( 'array', $value->limitationValues );
        self::assertEquals( $expected, $value->limitationValues );
    }

    protected function getVersionInfoMock()
    {
        $versionInfoMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo",
            array(),
            array(),
            '',
            false
        );
        $versionInfoMock
            ->expects( $this->once() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( 24 ) );

        return $versionInfoMock;
    }

    protected function getContentMock()
    {
        $contentMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Content",
            array(),
            array(),
            '',
            false
        );
        $contentMock
            ->expects( $this->once() )
            ->method( "getVersionInfo" )
            ->will( $this->returnValue( $this->getVersionInfoMock() ) );

        return $contentMock;
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return array(
            // VersionInfo, no access
            array(
                'limitation' => new StatusLimitation(),
                'object' => $this->getVersionInfoMock(),
                'expected' => false
            ),
            // VersionInfo, no access
            array(
                'limitation' => new StatusLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getVersionInfoMock(),
                'expected' => false
            ),
            // VersionInfo, with access
            array(
                'limitation' => new StatusLimitation( array( 'limitationValues' => array( 24 ) ) ),
                'object' => $this->getVersionInfoMock(),
                'expected' => true
            ),
            // Content, no access
            array(
                'limitation' => new StatusLimitation(),
                'object' => $this->getContentMock(),
                'expected' => false
            ),
            // Content, no access
            array(
                'limitation' => new StatusLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getContentMock(),
                'expected' => false
            ),
            // Content, with access
            array(
                'limitation' => new StatusLimitation( array( 'limitationValues' => array( 24 ) ) ),
                'object' => $this->getContentMock(),
                'expected' => true
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        StatusLimitation $limitation,
        ValueObject $object,
        $expected,
        StatusLimitationType $limitationType
    )
    {
        $userMock = $this->getUserMock();
        $userMock->expects( $this->never() )->method( $this->anything() );

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
        );

        self::assertInternalType( 'boolean', $value );
        self::assertEquals( $expected, $value );
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument()
    {
        $versionInfoMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo",
            array(),
            array(),
            '',
            false
        );
        return array(
            // invalid limitation
            array(
                'limitation' => new ObjectStateLimitation(),
                'object' => $versionInfoMock
            ),
            // invalid object
            array(
                'limitation' => new StatusLimitation(),
                'object' => new ObjectStateLimitation()
            ),
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        StatusLimitationType $limitationType
    )
    {
        $userMock = $this->getUserMock();
        $userMock->expects( $this->never() )->method( $this->anything() );

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
     * @param \eZ\Publish\Core\Limitation\StatusLimitationType $limitationType
     */
    public function testGetCriterion( StatusLimitationType $limitationType )
    {
        $limitationType->getCriterion( new StatusLimitation(), $this->getUserMock() );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\StatusLimitationType $limitationType
     */
    public function testValueSchema( StatusLimitationType $limitationType )
    {
        self::markTestSkipped( "Method valueSchema() is not implemented" );
    }
}
