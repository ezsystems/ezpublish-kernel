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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Limitation\BlockingLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;

/**
 * Test Case for LimitationType
 */
class BlockingLimitationTypeTest extends Base
{
    /**
     * @return \eZ\Publish\Core\Limitation\BlockingLimitationType
     */
    public function testConstruct()
    {
        return new BlockingLimitationType( 'Test' );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array( new BlockingLimitation( 'Test', array() ) ),
            array( new BlockingLimitation( 'FunctionList', array() ) )
        );
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testAcceptValue( BlockingLimitation $limitation, BlockingLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException()
    {
        return array(
            array( new ObjectStateLimitation() )
        );
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testAcceptValueException( Limitation $limitation, BlockingLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return array(
            array( new BlockingLimitation( 'Test', array( 'limitationValues' => array( 'ezjscore::call' ) ) ) ),
            array( new BlockingLimitation( 'Test', array( 'limitationValues' => array( 'ezjscore::call', 'my::call' ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation $limitation
     */
    public function testValidatePass( BlockingLimitation $limitation )
    {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate( $limitation );
        self::assertEmpty( $validationErrors );
    }

    /**
     * @return array
     */
    public function providerForTestValidateError()
    {
        return array(
            array( new BlockingLimitation( 'Test', array() ), 1 ),
            array( new BlockingLimitation( 'Test', array( 'limitationValues' => array( 0 ) ) ), 0 ),
            array( new BlockingLimitation( 'Test', array( 'limitationValues' => array( 0, PHP_INT_MAX ) ) ), 0 ),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError( BlockingLimitation $limitation, $errorCount )
    {
        $this->getPersistenceMock()
                ->expects( $this->never() )
                ->method( $this->anything() );

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate( $limitation );
        self::assertCount( $errorCount, $validationErrors );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testBuildValue( BlockingLimitationType $limitationType )
    {
        $expected = array( 'test', 'test' => 9 );
        $value = $limitationType->buildValue( $expected );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation\BlockingLimitation', $value );
        self::assertInternalType( 'array', $value->limitationValues );
        self::assertEquals( $expected, $value->limitationValues );
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return array(
            // ContentInfo, no access
            array(
                'limitation' => new BlockingLimitation( 'Test', array() ),
                'object' => new ContentInfo(),
                'targets' => array()
            ),
            // ContentInfo, no access
            array(
                'limitation' => new BlockingLimitation( 'Test', array( 'limitationValues' => array( 2 ) ) ),
                'object' => new ContentInfo(),
                'targets' => array()
            ),
            // ContentInfo, with access
            array(
                'limitation' => new BlockingLimitation( 'Test', array( 'limitationValues' => array( 66 ) ) ),
                'object' => new ContentInfo( array( 'contentTypeId' => 66 ) ),
                'targets' => array()
            ),
            // ContentCreateStruct, no access
            array(
                'limitation' => new BlockingLimitation( 'Test', array( 'limitationValues' => array( 2 ) ) ),
                'object' => new ContentCreateStruct( array( 'contentType' => ((object)array( 'id' => 22 ) ) ) ),
                'targets' => array()
            ),
            // ContentCreateStruct, with access
            array(
                'limitation' => new BlockingLimitation( 'Test', array( 'limitationValues' => array( 2, 43 ) ) ),
                'object' => new ContentCreateStruct( array( 'contentType' => ((object)array( 'id' => 43 ) ) ) ),
                'targets' => array()
            ),
        );
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        BlockingLimitation $limitation,
        ValueObject $object,
        array $targets
    )
    {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $persistenceMock = $this->getPersistenceMock();
        $persistenceMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );

        self::assertFalse( $value );
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
                'targets' => array( new Location() ),
            )
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
    )
    {
        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $userMock = $this->getUserMock();
        $userMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $persistenceMock = $this->getPersistenceMock();
        $persistenceMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $v = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );
        var_dump( $v );// intentional, debug in case no exception above
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testGetCriterion( BlockingLimitationType $limitationType )
    {
        $criterion = $limitationType->getCriterion(
            new BlockingLimitation( 'Test', array() ),
            $this->getUserMock()
        );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchNone', $criterion );
        self::assertInternalType( 'null', $criterion->value );
        self::assertInternalType( 'null', $criterion->operator );
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\BlockingLimitationType $limitationType
     */
    public function testValueSchema( BlockingLimitationType $limitationType )
    {
        self::assertEquals(
            array(),
            $limitationType->valueSchema()
        );
    }
}
