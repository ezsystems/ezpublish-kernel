<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId;

/**
 * Test case for Persistence\Cache\SearchHandler
 */
class SearchHandlerTest extends HandlerTest
{
    /**
     * @return array
     */
    public function providerForUnCachedMethods()
    {
        return array(
            array( 'findContent', array( new Query, array( 42 ) ) ),
            array( 'findSingle', array( new ContentTypeId( 4 ), array( 42 ) ) ),
            array( 'suggest', array( 'prefix%', array( 42 ), 10, new ContentTypeId( 4 ) ) ),
            array(
                'indexContent',
                array(
                    new Content(
                        array(
                            'versionInfo' => new VersionInfo(
                                array(
                                    'contentInfo' => new ContentInfo( array( 'id' => 42 ) )
                                )
                            )
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     */
    public function testUnCachedMethods( $method, array $arguments )
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Search\\Handler' );
        $this->persistenceHandlerMock
            ->expects( $this->once() )
            ->method( 'searchHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $expects = $innerHandler
            ->expects( $this->once() )
            ->method( $method );

        if ( isset( $arguments[2] ) )
            $expects->with( $arguments[0], $arguments[1], $arguments[2] );
        else if ( isset( $arguments[1] ) )
            $expects->with( $arguments[0], $arguments[1] );
        else if ( isset( $arguments[0] ) )
            $expects->with( $arguments[0] );

        $expects->will( $this->returnValue( null ) );

        $handler = $this->persistenceCacheHandler->searchHandler();
        call_user_func_array( array( $handler, $method ), $arguments );
    }
}
