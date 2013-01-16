<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for Persistence\Cache\ContentHandler
 */
class ContentHandlerTest extends HandlerTest
{
    /**
     * @return array
     */
    function providerForUnCachedMethods()
    {
        return array(
            array( 'create', array( new CreateStruct ) ),
            array( 'createDraftFromVersion', array( 2, 1, 14 ) ),
            array( 'copy', array( 2, 1 ) ),
            array( 'load', array( 2, 1, 'eng-GB' ) ),
            array( 'loadContentInfo', array( 2 ) ),
            array( 'loadVersionInfo', array( 2, 1 ) ),
            array( 'loadDraftsForUser', array( 14 ) ),
            array( 'setStatus', array( 2, 0, 1 ) ),
            array( 'updateMetadata', array( 2, new MetadataUpdateStruct ) ),
            array( 'updateContent', array( 2, 1, new UpdateStruct ) ),
            array( 'deleteContent', array( 2 ) ),
            array( 'deleteVersion', array( 2, 1 ) ),
            array( 'listVersions', array( 2 ) ),
            array( 'addRelation', array( new RelationCreateStruct ) ),
            array( 'removeRelation', array( 66 ) ),
            array( 'loadRelations', array( 2, 1, 3 ) ),
            array( 'loadReverseRelations', array( 2, 3 ) ),
            array( 'publish', array( 2, 3, new MetadataUpdateStruct ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler
     */
    public function testUnCachedMethods( $method, array $arguments )
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentHandler' )
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

        $handler = $this->persistenceHandler->contentHandler();
        call_user_func_array( array( $handler, $method ), $arguments );
    }

}
