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
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\ContentTypeLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;

/**
 * Test Case for LimitationType
 */
class ContentTypeLimitationTypeTest extends Base
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contentTypeHandlerMock;

    /**
     * Setup Location Handler mock
     */
    public function setUp()
    {
        parent::setUp();

        $this->contentTypeHandlerMock = $this->getMock(
            "eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler",
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * Tear down Location Handler mock
     */
    public function tearDown()
    {
        unset( $this->contentTypeHandlerMock );
        parent::tearDown();
    }

    /**
     *
     * @return \eZ\Publish\Core\Limitation\ContentTypeLimitationType
     */
    public function testConstruct()
    {
        return new ContentTypeLimitationType( $this->getPersistenceMock() );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array( new ContentTypeLimitation() ),
            array( new ContentTypeLimitation( array() ) ),
            array( new ContentTypeLimitation( array( 'limitationValues' => array( 0, PHP_INT_MAX, '2', 's3fdaf32r' ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testAcceptValue( ContentTypeLimitation $limitation, ContentTypeLimitationType $limitationType )
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
            array( new ContentTypeLimitation( array( 'limitationValues' => array( true ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testAcceptValueException( Limitation $limitation, ContentTypeLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return array(
            array( new ContentTypeLimitation() ),
            array( new ContentTypeLimitation( array() ) ),
            array( new ContentTypeLimitation( array( 'limitationValues' => array( 2 ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation $limitation
     */
    public function testValidatePass( ContentTypeLimitation $limitation )
    {
        if ( !empty( $limitation->limitationValues ) )
        {
            $this->getPersistenceMock()
                ->expects( $this->any() )
                ->method( "contentTypeHandler" )
                ->will( $this->returnValue( $this->contentTypeHandlerMock ) );

            foreach ( $limitation->limitationValues as $key => $value )
            {
                $this->contentTypeHandlerMock
                    ->expects( $this->at( $key ) )
                    ->method( "load" )
                    ->with( $value );
            }
        }

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
            array( new ContentTypeLimitation(), 0 ),
            array( new ContentTypeLimitation( array( 'limitationValues' => array( 0 ) ) ), 1 ),
            array( new ContentTypeLimitation( array( 'limitationValues' => array( 0, PHP_INT_MAX ) ) ), 2 ),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError( ContentTypeLimitation $limitation, $errorCount )
    {
        if ( !empty( $limitation->limitationValues ) )
        {
            $this->getPersistenceMock()
                ->expects( $this->any() )
                ->method( "contentTypeHandler" )
                ->will( $this->returnValue( $this->contentTypeHandlerMock ) );

            foreach ( $limitation->limitationValues as $key => $value )
            {
                $this->contentTypeHandlerMock
                    ->expects( $this->at( $key ) )
                    ->method( "load" )
                    ->with( $value )
                    ->will( $this->throwException( new NotFoundException( 'contentType', $value ) ) );
            }
        }
        else
        {
            $this->getPersistenceMock()
                ->expects( $this->never() )
                ->method( $this->anything() );
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate( $limitation );
        self::assertCount( $errorCount, $validationErrors );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testBuildValue( ContentTypeLimitationType $limitationType )
    {
        $expected = array( 'test', 'test' => 9 );
        $value = $limitationType->buildValue( $expected );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation', $value );
        self::assertInternalType( 'array', $value->limitationValues );
        self::assertEquals( $expected, $value->limitationValues );
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        // Mocks for testing Content & VersionInfo objects, should only be used once because of expect rules.
        $contentMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Content",
            array(),
            array(),
            '',
            false
        );

        $versionInfoMock = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo",
            array(),
            array(),
            '',
            false
        );

        $contentMock
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfoMock ) );

        $versionInfoMock
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( new ContentInfo( array( 'contentTypeId' => 66 ) ) ) );

        $versionInfoMock2 = $this->getMock(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo",
            array(),
            array(),
            '',
            false
        );

        $versionInfoMock2
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( new ContentInfo( array( 'contentTypeId' => 66 ) ) ) );

        return array(
            // ContentInfo, no access
            array(
                'limitation' => new ContentTypeLimitation(),
                'object' => new ContentInfo(),
                'targets' => array(),
                'expected' => false
            ),
            // ContentInfo, no access
            array(
                'limitation' => new ContentTypeLimitation( array( 'limitationValues' => array( 2 ) ) ),
                'object' => new ContentInfo(),
                'targets' => array(),
                'expected' => false
            ),
            // ContentInfo, with access
            array(
                'limitation' => new ContentTypeLimitation( array( 'limitationValues' => array( 66 ) ) ),
                'object' => new ContentInfo( array( 'contentTypeId' => 66 ) ),
                'targets' => array(),
                'expected' => true
            ),
            // Content, with access
            array(
                'limitation' => new ContentTypeLimitation( array( 'limitationValues' => array( 66 ) ) ),
                'object' => $contentMock,
                'targets' => array(),
                'expected' => true
            ),
            // VersionInfo, with access
            array(
                'limitation' => new ContentTypeLimitation( array( 'limitationValues' => array( 66 ) ) ),
                'object' => $versionInfoMock2,
                'targets' => array(),
                'expected' => true
            ),
            // ContentCreateStruct, no access
            array(
                'limitation' => new ContentTypeLimitation( array( 'limitationValues' => array( 2 ) ) ),
                'object' => new ContentCreateStruct( array( 'contentType' => ((object)array( 'id' => 22 ) ) ) ),
                'targets' => array(),
                'expected' => false
            ),
            // ContentCreateStruct, with access
            array(
                'limitation' => new ContentTypeLimitation( array( 'limitationValues' => array( 2, 43 ) ) ),
                'object' => new ContentCreateStruct( array( 'contentType' => ((object)array( 'id' => 43 ) ) ) ),
                'targets' => array(),
                'expected' => true
            ),
        );
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        ContentTypeLimitation $limitation,
        ValueObject $object,
        array $targets,
        $expected
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

        self::assertInternalType( 'boolean', $value );
        self::assertEquals( $expected, $value );
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
            ),
            // invalid object
            array(
                'limitation' => new ContentTypeLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => array( new Location() ),
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
        var_dump( $v );
    }

    /**
     * @depends testConstruct
     * @expectedException \RuntimeException
     *
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue( ContentTypeLimitationType $limitationType )
    {
        $limitationType->getCriterion(
            new ContentTypeLimitation( array() ),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testGetCriterionSingleValue( ContentTypeLimitationType $limitationType )
    {
        $criterion = $limitationType->getCriterion(
            new ContentTypeLimitation( array( 'limitationValues' => array( 9 ) ) ),
            $this->getUserMock()
        );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId', $criterion );
        self::assertInternalType( 'array', $criterion->value );
        self::assertInternalType( 'string', $criterion->operator );
        self::assertEquals( Operator::EQ, $criterion->operator );
        self::assertEquals( array( 9 ), $criterion->value );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testGetCriterionMultipleValues( ContentTypeLimitationType $limitationType )
    {
        $criterion = $limitationType->getCriterion(
            new ContentTypeLimitation( array( 'limitationValues' => array( 9, 55 ) ) ),
            $this->getUserMock()
        );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId', $criterion );
        self::assertInternalType( 'array', $criterion->value );
        self::assertInternalType( 'string', $criterion->operator );
        self::assertEquals( Operator::IN, $criterion->operator );
        self::assertEquals( array( 9, 55 ), $criterion->value );
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\ContentTypeLimitationType $limitationType
     */
    public function testValueSchema( ContentTypeLimitationType $limitationType )
    {
        self::assertEquals(
            ContentTypeLimitationType::VALUE_SCHEMA_LOCATION_ID,
            $limitationType->valueSchema()
        );
    }
}
