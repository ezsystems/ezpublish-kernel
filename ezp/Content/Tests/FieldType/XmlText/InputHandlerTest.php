<?php
/**
 * File containing the ezp\Content\Tests\FieldType\XmlText\InputHandlerTest class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType\XmlText;

use ezp\Content\FieldType\XmlText\Input\Handler as InputHandler,
    PHPUnit_Framework_TestCase,
    DOMDocument;

class InputHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testIsXmlValid()
    {
        $inputParser = $this->getInputParserMock();
        $inputParser
            ->expects( $this->once() )
            ->method( 'process' )
            ->will( $this->returnValue( new DOMDocument ) );

        $handler = new InputHandler( $inputParser );
        self::assertTrue( $handler->isXmlValid( '', false ) );
    }

    public function testIsXmlInvalid()
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
        $idArray = array( 1, 2, 3 );

        $repository = $this->getMockBuilder( '\\ezp\\Base\\Repository' )
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryHandler = $this->getMock( 'ezp\\Persistence\\Repository\\Handler' );

        $fieldTypeService = $this->getMockBuilder( 'ezp\\Content\\FieldType\\Service' )
            ->setConstructorArgs( array( $repository, $repositoryHandler ) )
            ->getMock();

        $repository
            ->expects( $this->once() )
            ->method( 'getInternalFieldTypeService' )
            ->will( $this->returnValue( $fieldTypeService ) );

        $version = $this->getMock( 'ezp\\Content\\Version' );

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
     * Returns a mock object for {@see \ezp\Content\FieldType\XmlText\Input\Parser}
     * @return \ezp\Content\FieldType\XmlText\Input\Parser
     */
    public function getInputParserMock()
    {
        return $this->getMock( 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser' );
    }
}
