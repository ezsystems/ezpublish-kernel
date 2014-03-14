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
use eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\ParentContentTypeLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;

/**
 * Test Case for LimitationType
 */
class ParentContentTypeLimitationTest extends Base
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locationHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contentTypeHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contentHandlerMock;

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
        $this->contentTypeHandlerMock = $this->getMock(
            "eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler",
            array(),
            array(),
            '',
            false
        );
        $this->contentHandlerMock = $this->getMock(
            "eZ\\Publish\\SPI\\Persistence\\Content\\Handler",
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
        unset( $this->contentTypeHandlerMock );
        unset( $this->contentHandlerMock );
        parent::tearDown();
    }

    /**
     *
     * @return \eZ\Publish\Core\Limitation\ParentContentTypeLimitationType
     */
    public function testConstruct()
    {
        return new ParentContentTypeLimitationType( $this->getPersistenceMock() );
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue()
    {
        return array(
            array( new ParentContentTypeLimitation() ),
            array( new ParentContentTypeLimitation( array() ) ),
            array( new ParentContentTypeLimitation( array( 'limitationValues' => array( '', 'true', '2', 's3fd4af32r' ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValue
     * @depends testConstruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation $limitation
     * @param \eZ\Publish\Core\Limitation\ParentContentTypeLimitationType $limitationType
     */
    public function testAcceptValue( ParentContentTypeLimitation $limitation, ParentContentTypeLimitationType $limitationType )
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
            array( new ParentContentTypeLimitation( array( 'limitationValues' => array( true ) ) ) ),
            array( new ParentContentTypeLimitation( array( 'limitationValues' => array( new \DateTime ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\Core\Limitation\ParentContentTypeLimitationType $limitationType
     */
    public function testAcceptValueException( Limitation $limitation, ParentContentTypeLimitationType $limitationType )
    {
        $limitationType->acceptValue( $limitation );
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass()
    {
        return array(
            array( new ParentContentTypeLimitation() ),
            array( new ParentContentTypeLimitation( array() ) ),
            array( new ParentContentTypeLimitation( array( 'limitationValues' => array( '1' ) ) ) ),
        );
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation $limitation
     */
    public function testValidatePass( ParentContentTypeLimitation $limitation )
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
                    ->will( $this->returnValue( 42 ) );
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
            array( new ParentContentTypeLimitation(), 0 ),
            array( new ParentContentTypeLimitation( array( 'limitationValues' => array( '/1/777/' ) ) ), 1 ),
            array( new ParentContentTypeLimitation( array( 'limitationValues' => array( '/1/888/', '/1/999/' ) ) ), 2 ),
        );
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError( ParentContentTypeLimitation $limitation, $errorCount )
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
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\ParentContentTypeLimitationType $limitationType
     */
    public function testBuildValue( ParentContentTypeLimitationType $limitationType )
    {
        $expected = array( 'test', 'test' => '1' );
        $value = $limitationType->buildValue( $expected );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation', $value );
        self::assertInternalType( 'array', $value->limitationValues );
        self::assertEquals( $expected, $value->limitationValues );
    }

    protected function getTestEvaluateContentMock()
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
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $this->getTestEvaluateVersionInfoMock() ) );

        return $contentMock;
    }

    protected function getTestEvaluateVersionInfoMock()
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
            ->method( 'getContentInfo' )
            ->will( $this->returnValue( new ContentInfo( array( 'published' => true ) ) ) );

        return $versionInfoMock;
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate()
    {
        return array(
            // ContentInfo, with API targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation(),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new Location( array( "contentInfo" => new ContentInfo( array( "contentTypeId" => 24 ) ) ) ) ),
                'persistence' => array(),
                'expected' => false
            ),
            // ContentInfo, with SPI targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation(),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new SPILocation( array( "contentId" => 42 ) ) ),
                'persistence' => array(
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "24" ) ) )
                ),
                'expected' => false
            ),
            // ContentInfo, with API targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new Location( array( "contentInfo" => new ContentInfo( array( "contentTypeId" => 24 ) ) ) ) ),
                'persistence' => array(),
                'expected' => false
            ),
            // ContentInfo, with SPI targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new SPILocation( array( "contentId" => 42 ) ) ),
                'persistence' => array(
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "24" ) ) )
                ),
                'expected' => false
            ),
            // ContentInfo, with API targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new Location( array( "contentInfo" => new ContentInfo( array( "contentTypeId" => 42 ) ) ) ) ),
                'persistence' => array(),
                'expected' => true
            ),
            // ContentInfo, with SPI targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array( new SPILocation( array( "contentId" => 24 ) ) ),
                'persistence' => array(
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "42" ) ) )
                ),
                'expected' => true
            ),
            // ContentInfo, no targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array(),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => '24' ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "42" ) ) )
                ),
                'expected' => true
            ),
            // ContentInfo, no targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => true ) ),
                'targets' => array(),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => '24' ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "4200" ) ) )
                ),
                'expected' => false
            ),
            // ContentInfo, no targets, un-published, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => false ) ),
                'targets' => array(),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => '24' ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "42" ) ) )
                ),
                'expected' => true
            ),
            // ContentInfo, no targets, un-published, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentInfo( array( 'published' => false ) ),
                'targets' => array(),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => '24' ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "4200" ) ) )
                ),
                'expected' => false
            ),
            // Content, with API targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getTestEvaluateContentMock(),
                'targets' => array( new Location( array( "contentInfo" => new ContentInfo( array( "contentTypeId" => 42 ) ) ) ) ),
                'persistence' => array(),
                'expected' => true
            ),
            // Content, with SPI targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getTestEvaluateContentMock(),
                'targets' => array( new SPILocation( array( 'contentId' => '24' ) ) ),
                'persistence' => array(
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "42" ) ) )
                ),
                'expected' => true
            ),
            // VersionInfo, with API targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getTestEvaluateVersionInfoMock(),
                'targets' => array( new Location( array( "contentInfo" => new ContentInfo( array( "contentTypeId" => 42 ) ) ) ) ),
                'persistence' => array(),
                'expected' => true
            ),
            // VersionInfo, with SPI targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getTestEvaluateVersionInfoMock(),
                'targets' => array( new SPILocation( array( 'contentId' => '24' ) ) ),
                'persistence' => array(
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "42" ) ) )
                ),
                'expected' => true
            ),
            // VersionInfo, with LocationCreateStruct targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getTestEvaluateVersionInfoMock(),
                'targets' => array( new LocationCreateStruct( array( 'parentLocationId' => 24 ) ) ),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => 100 ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "42" ) ) )
                ),
                'expected' => true
            ),
            // Content, with LocationCreateStruct targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => $this->getTestEvaluateContentMock(),
                'targets' => array( new LocationCreateStruct( array( 'parentLocationId' => 24 ) ) ),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => 100 ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( "contentTypeId" => "24" ) ) )
                ),
                'expected' => false
            ),
            // ContentCreateStruct, no targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 42 ) ) ),
                'object' => new ContentCreateStruct(),
                'targets' => array(),
                'persistence' => array(),
                'expected' => false
            ),
            // ContentCreateStruct, with LocationCreateStruct targets, no access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 12, 23 ) ) ),
                'object' => new ContentCreateStruct(),
                'targets' => array( new LocationCreateStruct( array( 'parentLocationId' => 24 ) ) ),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => 100 ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( 'contentTypeId' => 34 ) ) )
                ),
                'expected' => false
            ),
            // ContentCreateStruct, with LocationCreateStruct targets, with access
            array(
                'limitation' => new ParentContentTypeLimitation( array( 'limitationValues' => array( 12, 23 ) ) ),
                'object' => new ContentCreateStruct(),
                'targets' => array( new LocationCreateStruct( array( 'parentLocationId' => 43 ) ) ),
                'persistence' => array(
                    "locations" => array( new SPILocation( array( 'contentId' => 100 ) ) ),
                    "contentInfos" => array( new SPIContentInfo( array( 'contentTypeId' => 12 ) ) )
                ),
                'expected' => true
            ),
        );
    }

    protected function assertContentHandlerExpectations( $callNo, $persistenceCalled, $contentId, $contentInfo )
    {
        $this->getPersistenceMock()
            ->expects( $this->at( $callNo + ( $persistenceCalled ? 1 : 0 ) ) )
            ->method( "contentHandler" )
            ->will( $this->returnValue( $this->contentHandlerMock ) );

        $this->contentHandlerMock
            ->expects( $this->at( $callNo ) )
            ->method( "loadContentInfo" )
            ->with( $contentId )
            ->will( $this->returnValue( $contentInfo ) );
    }

    /**
     * @dataProvider providerForTestEvaluate
     */
    public function testEvaluate(
        ParentContentTypeLimitation $limitation,
        ValueObject $object,
        $targets,
        array $persistence,
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
        // ContentTypeHandler is never used in evaluate()
        $persistenceMock
            ->expects( $this->never() )
            ->method( "contentTypeHandler" );

        if ( empty( $persistence ) )
        {
            // Covers API targets, where no additional loading is required
            $persistenceMock
                ->expects( $this->never() )
                ->method( $this->anything() );
        }
        else if ( !empty( $targets ) )
        {
            foreach ( $targets as $index => $target )
            {
                if ( $target instanceof LocationCreateStruct )
                {
                    $this->getPersistenceMock()
                        ->expects( $this->once( $index ) )
                        ->method( "locationHandler" )
                        ->will( $this->returnValue( $this->locationHandlerMock ) );

                    $this->locationHandlerMock
                        ->expects( $this->at( $index ) )
                        ->method( "load" )
                        ->with( $target->parentLocationId )
                        ->will( $this->returnValue( $location = $persistence["locations"][$index] ) );

                    $contentId = $location->contentId;
                }
                else
                {
                    $contentId = $target->contentId;
                }

                $this->assertContentHandlerExpectations(
                    $index,
                    $target instanceof LocationCreateStruct,
                    $contentId,
                    $persistence["contentInfos"][$index]
                );
            }
        }
        else
        {
            $this->getPersistenceMock()
                ->expects( $this->at( 0 ) )
                ->method( "locationHandler" )
                ->will( $this->returnValue( $this->locationHandlerMock ) );

            $this->locationHandlerMock
                ->expects( $this->once() )
                ->method(
                    $object instanceof ContentInfo && $object->published ?
                        "loadLocationsByContent" :
                        "loadParentLocationsForDraftContent"
                )
                ->with( $object->id )
                ->will( $this->returnValue( $persistence["locations"] ) );

            foreach ( $persistence["locations"] as $index => $location )
            {
                $this->assertContentHandlerExpectations(
                    $index,
                    true,
                    $location->contentId,
                    $persistence["contentInfos"][$index]
                );
            }
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
                'limitation' => new ParentContentTypeLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => array(),
                'persistence' => array(),
            ),
            // invalid target when using ContentCreateStruct
            array(
                'limitation' => new ParentContentTypeLimitation(),
                'object' => new ContentCreateStruct(),
                'targets' => array( new Location() ),
                'persistence' => array(),
            ),
            // invalid target when not using ContentCreateStruct
            array(
                'limitation' => new ParentContentTypeLimitation(),
                'object' => new ContentInfo(),
                'targets' => array( new ObjectStateLimitation() ),
                'persistence' => array(),
            ),
        );
    }

    /**
     * @dataProvider providerForTestEvaluateInvalidArgument
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument( Limitation $limitation, ValueObject $object, $targets )
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

        $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\Core\Limitation\ParentContentTypeLimitationType $limitationType
     */
    public function testGetCriterionInvalidValue( ParentContentTypeLimitationType $limitationType )
    {
        $limitationType->getCriterion(
            new ParentContentTypeLimitation( array() ),
            $this->getUserMock()
        );
    }

    /**
     * @depends testConstruct
     *
     * @param \eZ\Publish\Core\Limitation\ParentContentTypeLimitationType $limitationType
     */
    public function testValueSchema( ParentContentTypeLimitationType $limitationType )
    {
        $this->markTestIncomplete( "Method is not implemented yet: " . __METHOD__ );
    }
}
