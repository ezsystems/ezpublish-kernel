<?php
/**
 * File containing the ezp\Content\Tests\FieldType\XmlText\InputHandlerTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType\XmlText;

use eZ\Publish\Core\Repository\FieldType\XmlText\Input\Handler as InputHandler,
    ezp\Content\Relation,

    PHPUnit_Framework_TestCase,
    DOMDocument;

class InputHandlerTest extends PHPUnit_Framework_TestCase
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
        // List of ids returned by the parser
        $idArray = array( 1, 2, 3 );

        // Version DO
        $version = $this->getMock( 'ezp\\Content\\Version' );
        $version->id = 1;
        $version->contentId = 1;
        $version->versionNo = 1;

        $repository = $this->getMockBuilder( '\\ezp\\Base\\Repository' )
            ->disableOriginalConstructor()
            ->getMock();

        $persistenceHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Handler' );

        $fieldTypeService = $this->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\FieldType\\Service' )
            ->setConstructorArgs( array( $repository, $persistenceHandler ) )
            ->getMock();

        // 6 calls to FieldType\Service::addRelation())
        $fieldTypeService
            ->expects( $this->exactly( 6 ) )
            ->method( 'addRelation' )
            ->with(
                $this->logicalOr( $this->equalTo( Relation::ATTRIBUTE ), $this->equalTo( Relation::LINK ) ),
                $version->contentId,
                $version->id,
                $this->logicalOr( $this->equalTo( 1 ), $this->equalTo( 2 ), $this->equalTo( 3 ) )
            );

        $repository
            ->expects( $this->once() )
            ->method( 'getInternalFieldTypeService' )
            ->will( $this->returnValue( $fieldTypeService ) );

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
        self::assertTrue( $handler->process( '', $repository, $version ) );
    }

    /**
     * Returns a mock object for {@see \eZ\Publish\Core\Repository\FieldType\XmlText\Input\Parser}
     * @return \eZ\Publish\Core\Repository\FieldType\XmlText\Input\Parser
     */
    public function getInputParserMock()
    {
        return $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\XmlText\\Input\\Parser' );
    }
}
