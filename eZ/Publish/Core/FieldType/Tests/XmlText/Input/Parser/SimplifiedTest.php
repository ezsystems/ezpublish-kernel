<?php
/**
 * File containing the XmlText Simplified Parser test
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Input\Parser;

use eZ\Publish\Core\FieldType\XmlText\Input\Parser\Simplified as Parser,
    eZ\Publish\Core\FieldType\XmlText\Schema,
    PHPUnit_Framework_TestCase,
    DOMDocument;

class SimplifiedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\XmlText\Input\Parser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser( new Schema );
        $handler = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Handler' )
            ->setConstructorArgs( array( $this->parser ) )
            ->getMock();
    }

    /**
     * @dataProvider providerForTestProcess
     */
    public function testProcess( $xmlString, $domString )
    {
        $document = $this->parser->process( $xmlString );
        self::assertEquals( $domString, $document->saveXML() );
    }

    public function providerForTestProcess()
    {
        return array( array( '', '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"/>
' ) );
    }
}
