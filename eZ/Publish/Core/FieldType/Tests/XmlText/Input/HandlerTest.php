<?php
/**
 * File containing XmlText handler test.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Input;

use eZ\Publish\Core\FieldType\XmlText\Input\Handler as InputHandler,
    eZ\Publish\API\Repository\Values\Content\Relation,
    PHPUnit_Framework_TestCase,
    DOMDocument;

class HandlerTest extends PHPUnit_Framework_TestCase
{
    public function testIsXmlValidValidXml()
    {
        $inputParser = $this->getInputParserMock();
        $inputParser
            ->expects( $this->once() )
            ->method( 'process' )
            ->will( $this->returnValue( new DOMDocument ) );

        $handler = new InputHandler( $inputParser );
        self::assertTrue( $handler->isXmlValid( '', false ) );
    }

    public function testIsXmlValidInvalidXml()
    {
        $inputParser = $this->getInputParserMock();
        $inputParser
            ->expects( $this->once() )
            ->method( 'process' )
            ->will( $this->returnValue( false ) );

        $handler = new InputHandler( $inputParser );
        self::assertFalse( $handler->isXmlValid( '', false ) );
    }

    /**
     * Tests processing of URL items found in the XML
     */
    public function testProcessRelatedContent()
    {
        $this->markTestIncomplete( "@todo: Renable when there is a field specifc addRelation api (Field Service)" );
        // List of ids returned by the parser
        $idArray = array( 1, 2, 3 );

        // Version DO
        $version = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\VersionInfo' );
        $version->id = 55;
        $version->versionNo = 3;

        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $content->id = 1;
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $version ) );

        $repository = $this->getMockBuilder( 'eZ\Publish\API\Repository\Repository' )
            ->disableOriginalConstructor()
            ->getMock();

        $persistenceHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Handler' );

        $contentService = $this->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\ContentService' )
            ->setConstructorArgs( array( $repository, $persistenceHandler ) )
            ->getMock();

        // 6 calls to ContentService::addRelation())
        $contentService
            ->expects( $this->exactly( 6 ) )
            ->method( 'addRelation' )
            ->with(
                $this->logicalOr( $this->equalTo( Relation::FIELD ), $this->equalTo( Relation::LINK ) ),
                $content->id,
                $version->versionNo,
                $this->logicalOr( $this->equalTo( 1 ), $this->equalTo( 2 ), $this->equalTo( 3 ) )
            );

        $repository
            ->expects( $this->once() )
            ->method( 'getContentService' )
            ->will( $this->returnValue( $contentService ) );

        $inputParser = $this->getInputParserMock();

        // Parser::process => DOMDocument
        $inputParser
            ->expects( $this->once() )
            ->method( 'process' )
            ->will( $this->returnValue( $this->getMock( '\\DOMDocument' ) ) );

        // Parser::getRelatedContentIdArray() => array
        $inputParser
            ->expects( $this->once() )
            ->method( 'getRelatedContentIdArray' )
            ->will( $this->returnValue( $idArray ) );

        // Parser::getLinkedContentIdArray() => array
        $inputParser
            ->expects( $this->once() )
            ->method( 'getLinkedContentIdArray' )
            ->will( $this->returnValue( $idArray ) );

        $handler = new InputHandler( $inputParser );
        self::assertTrue( $handler->process( '', $repository, $content ) );
    }

    /**
     * Returns a mock object for {@see \eZ\Publish\Core\FieldType\XmlText\Input\Parser}
     * @return \eZ\Publish\Core\FieldType\XmlText\Input\Parser
     */
    public function getInputParserMock()
    {
        return $this->getMock( 'eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser' );
    }
}
