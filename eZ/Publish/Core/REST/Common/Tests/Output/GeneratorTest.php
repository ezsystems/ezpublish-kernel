<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Tests\Output;

use eZ\Publish\API\REST\Common;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 */
abstract class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startDocument( 'test' );
    }

    public function testValidDocumentStartAfterReset()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->reset();
        $generator->startDocument( 'test' );

        $this->assertNotNull( $generator->endDocument( 'test' ) );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentNameEnd()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->endDocument( 'invalid' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidOuterElementStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startElement( 'element' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidElementEnd()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startElement( 'element' );
        $generator->endElement( 'invalid' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testGeneratorMultipleElements()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );
        $generator->endElement( 'element' );

        $generator->startElement( 'element' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testGeneratorMultipleStackedElements()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );

        $generator->startElement( 'stacked' );
        $generator->endElement( 'stacked' );

        $generator->startElement( 'stacked' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentEnd()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startElement( 'element' );
        $generator->endDocument( 'test' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeOuterStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startAttribute( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeDocumentStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startAttribute( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeListStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startElement( 'element' );
        $generator->startList( 'list' );
        $generator->startAttribute( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementOuterStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startValueElement( 'element', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementDocumentStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startValueElement( 'element', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementListStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startElement( 'element' );
        $generator->startList( 'list' );
        $generator->startValueElement( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListOuterStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startList( 'list' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListDocumentStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startList( 'list' );
    }

    /**
     * @expectedException \eZ\Publish\API\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListListStart()
    {
        $generator = new Common\Output\Generator\Xml();

        $generator->startDocument( 'test' );
        $generator->startElement( 'element' );
        $generator->startList( 'list' );
        $generator->startList( 'attribute', 'value' );
    }
}

