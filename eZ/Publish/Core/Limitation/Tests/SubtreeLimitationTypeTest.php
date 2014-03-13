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
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\SubtreeLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * Test Case for LimitationType
 */
class SubtreeLimitationTypeTest extends Base
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locationHandlerMock;

    /**
     * Setup Location Handler mock
     */
    public function setUp()
    {
        parent::setUp();

        $this->locationHandlerMock = $this->getMock(
            "eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler",
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
        unset( $this->locationHandlerMock );
        parent::tearDown();
    }

    /**
     *
     * @return \eZ\Publish\Core\Limitation\SubtreeLimitationType
     */
    public function testConstruct()
    {
        return new SubtreeLimitationType( $this->getPersistenceMock() );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array( new SubtreeLimitation() ),
            array( new SubtreeLimitation( array() ) ),
            array( new SubtreeLimitation( array( 'limitationValues' => array( '', 'true', '2', 's3fdaf32r' ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testAcceptValue( SubtreeLimitation $limitation, SubtreeLimitationType $limitationType )
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
            array( new SubtreeLimitation( array( 'limitationValues' => array( true ) ) ) ),
            array( new SubtreeLimitation( array( 'limitationValues' => array( 1 ) ) ) ),
            array( new SubtreeLimitation( array( 'limitationValues' => array( 0 ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testAcceptValueException( Limitation $limitation, SubtreeLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return array(
            array( new SubtreeLimitation() ),
            array( new SubtreeLimitation( array() ) ),
            array( new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation $limitation
     */
    public function testValidatePass( SubtreeLimitation $limitation )
    {
        if ( !empty( $limitation->limitationValues ) )
        {
            $this->getPersistenceMock()
                ->expects( $this->any() )
                ->method( "locationHandler" )
                ->will( $this->returnValue( $this->locationHandlerMock ) );

            foreach ( $limitation->limitationValues as $key => $value )
            {
                $pathArray = explode( '/', trim( $value, '/' ) );
                $this->locationHandlerMock
                    ->expects( $this->at( $key ) )
                    ->method( "load" )
                    ->with( end( $pathArray ) )
                    ->will(
                        $this->returnValue(
                            new SPILocation( array( "pathString" => $value ) )
                        )
                    );
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
            array( new SubtreeLimitation(), 0 ),
            array( new SubtreeLimitation( array( 'limitationValues' => array( '/1/777/' ) ) ), 1 ),
            array( new SubtreeLimitation( array( 'limitationValues' => array( '/1/888/', '/1/999/' ) ) ), 2 ),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError( SubtreeLimitation $limitation, $errorCount )
    {
        if ( !empty( $limitation->limitationValues ) )
        {
            $this->getPersistenceMock()
                ->expects( $this->any() )
                ->method( "locationHandler" )
                ->will( $this->returnValue( $this->locationHandlerMock ) );

            foreach ( $limitation->limitationValues as $key => $value )
            {
                $pathArray = explode( '/', trim( $value, '/' ) );
                $this->locationHandlerMock
                    ->expects( $this->at( $key ) )
                    ->method( "load" )
                    ->with( end( $pathArray ) )
                    ->will( $this->throwException( new NotFoundException( 'location', $value ) ) );
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
     */
    public function testValidateErrorWrongPath()
    {
        $limitation = new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/42/' ) ) );

        $this->getPersistenceMock()
            ->expects( $this->any() )
            ->method( "locationHandler" )
            ->will( $this->returnValue( $this->locationHandlerMock ) );

        foreach ( $limitation->limitationValues as $key => $value )
        {
            $pathArray = explode( '/', trim( $value, '/' ) );
            $this->locationHandlerMock
                ->expects( $this->at( $key ) )
                ->method( "load" )
                ->with( end( $pathArray ) )
                ->will(
                    $this->returnValue(
                        new SPILocation( array( "pathString" => "/1/5/42" ) )
                    )
                );
        }

        // Need to create inline instead of depending on testConstruct() to get correct mock instance
        $limitationType = $this->testConstruct();

        $validationErrors = $limitationType->validate( $limitation );
        self::assertCount( 1, $validationErrors );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testBuildValue( SubtreeLimitationType $limitationType )
    {
        $expected = array( 'test', 'test' => '/1/999/' );
        $value = $limitationType->buildValue( $expected );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation', $value );
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
            ->will( $this->returnValue( new ContentInfo( array( 'published' => true ) ) ) );

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
            ->will( $this->returnValue( new ContentInfo( array( 'published' => true ) ) ) );

        return array(
            // ContentInfo, with targets, no access
            array(
                'limitation' => new SubtreeLimitation(),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new Location() ),
                'persistence' => array(),
                'expected' => false
            ),
            // ContentInfo, with targets, no access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new Location( array( 'pathString' => '/1/55/' ) ) ),
                'persistence' => array(),
                'expected' => false
            ),
            // ContentInfo, with targets, with access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new Location( array( 'pathString' => '/1/2/' ) ) ),
                'persistence' => array(),
                'expected' => true
            ),
            // ContentInfo, no targets, with access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => null,
                'persistence' => array( new Location( array( 'pathString' => '/1/2/' ) ) ),
                'expected' => true
            ),
            // ContentInfo, no targets, no access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/', '/1/43/' ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => null,
                'persistence' => array( new Location( array( 'pathString' => '/1/55/' ) ) ),
                'expected' => false
            ),
            // ContentInfo, no targets, un-published, with access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => new ContentInfo( array( 'published' => false ) ),
                'targets' => null,
                'persistence' => array( new Location( array( 'pathString' => '/1/2/' ) ) ),
                'expected' => true
            ),
            // ContentInfo, no targets, un-published, no access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/', '/1/43/' ) ) ),
                'object' => new ContentInfo( array( 'published' => false ) ),
                'targets' => null,
                'persistence' => array( new Location( array( 'pathString' => '/1/55/' ) ) ),
                'expected' => false
            ),
            // Content, with targets, with access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => $contentMock,
                'targets' => array( new Location( array( 'pathString' => '/1/2/' ) ) ),
                'persistence' => array(),
                'expected' => true
            ),
            // VersionInfo, with targets, with access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => $versionInfoMock2,
                'targets' => array( new Location( array( 'pathString' => '/1/2/' ) ) ),
                'persistence' => array(),
                'expected' => true
            ),
            // ContentCreateStruct, no targets, no access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/' ) ) ),
                'object' => new ContentCreateStruct(),
                'targets' => array(),
                'persistence' => array(),
                'expected' => false
            ),
            // ContentCreateStruct, with targets, no access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/', '/1/43/' ) ) ),
                'object' => new ContentCreateStruct(),
                'targets' => array( new LocationCreateStruct( array( 'parentLocationId' => 55 ) ) ),
                'persistence' => array( new Location( array( 'pathString' => '/1/55/' ) ) ),
                'expected' => false
            ),
            // ContentCreateStruct, with targets, with access
            array(
                'limitation' => new SubtreeLimitation( array( 'limitationValues' => array( '/1/2/', '/1/43/' ) ) ),
                'object' => new ContentCreateStruct(),
                'targets' => array( new LocationCreateStruct( array( 'parentLocationId' => 43 ) ) ),
                'persistence' => array( new Location( array( 'pathString' => '/1/43/' ) ) ),
                'expected' => true
            ),
            // invalid target
            array(
                'limitation' => new SubtreeLimitation(),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new ObjectStateLimitation() ),
                'persistence' => array(),
                'expected' => false
            ),
        );
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        SubtreeLimitation $limitation,
        ValueObject $object,
        $targets,
        array $persistenceLocations,
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
        if ( empty( $persistenceLocations ) && $targets !== null )
        {
            $persistenceMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else if ( $object instanceof ContentCreateStruct )
        {
            foreach ( (array)$targets as $key => $target )
            {
                $this->getPersistenceMock()
                    ->expects( $this->at( $key ) )
                    ->method( "locationHandler" )
                    ->will( $this->returnValue( $this->locationHandlerMock ) );

                $this->locationHandlerMock
                    ->expects( $this->at( $key ) )
                    ->method( "load" )
                    ->with( $target->parentLocationId )
                    ->will( $this->returnValue( $persistenceLocations[$key] ) );
            }
        }
        else
        {
            $this->getPersistenceMock()
                ->expects( $this->once() )
                ->method( "locationHandler" )
                ->will( $this->returnValue( $this->locationHandlerMock ) );

            $this->locationHandlerMock
                ->expects( $this->once() )
                ->method( $object instanceof ContentInfo && $object->published ? "loadLocationsByContent" : "loadParentLocationsForDraftContent" )
                ->with( $object->id )
                ->will( $this->returnValue( $persistenceLocations ) );
        }

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
                'persistence' => array(),
            ),
            // invalid object
            array(
                'limitation' => new SubtreeLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => array( new Location() ),
                'persistence' => array(),
            ),
            // invalid target when using ContentCreateStruct
            array(
                'limitation' => new SubtreeLimitation(),
                'object' => new ContentCreateStruct(),
                'targets' => array( new Location() ),
                'persistence' => array(),
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
        $targets,
        array $persistenceLocations
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
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue( SubtreeLimitationType $limitationType )
    {
        $limitationType->getCriterion(
            new SubtreeLimitation( array() ),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testGetCriterionSingleValue( SubtreeLimitationType $limitationType )
    {
        $criterion = $limitationType->getCriterion(
            new SubtreeLimitation( array( 'limitationValues' => array( '/1/9/' ) ) ),
            $this->getUserMock()
        );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree', $criterion );
        self::assertInternalType( 'array', $criterion->value );
        self::assertInternalType( 'string', $criterion->operator );
        self::assertEquals( Operator::EQ, $criterion->operator );
        self::assertEquals( array( '/1/9/' ), $criterion->value );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testGetCriterionMultipleValues( SubtreeLimitationType $limitationType )
    {
        $criterion = $limitationType->getCriterion(
            new SubtreeLimitation( array( 'limitationValues' => array( '/1/9/', '/1/55/' ) ) ),
            $this->getUserMock()
        );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree', $criterion );
        self::assertInternalType( 'array', $criterion->value );
        self::assertInternalType( 'string', $criterion->operator );
        self::assertEquals( Operator::IN, $criterion->operator );
        self::assertEquals( array( '/1/9/', '/1/55/' ), $criterion->value );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\SubtreeLimitationType $limitationType
     */
    public function testValueSchema( SubtreeLimitationType $limitationType )
    {
        self::assertEquals(
            SubtreeLimitationType::VALUE_SCHEMA_LOCATION_PATH,
            $limitationType->valueSchema()
        );
    }
}
