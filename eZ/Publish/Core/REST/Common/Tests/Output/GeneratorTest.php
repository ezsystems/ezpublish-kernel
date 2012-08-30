<?php
/**
 * File containing the GeneratorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\Core\REST\Common;

/**
 * Output generator test class
 */
abstract class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $xmlGenerator;
    protected $jsonGenerator;

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startDocument( 'test' );
    }

    public function testValidDocumentStartAfterReset()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->reset();
        $generator->startDocument( 'test' );

        $this->assertNotNull( $generator->endDocument( 'test' ) );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentNameEnd()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->endDocument( 'invalid' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidOuterElementStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startObjectElement( 'element' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidElementEnd()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startObjectElement( 'element' );
        $generator->endObjectElement( 'invalid' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testGeneratorMultipleElements()
    {
        $generator = $this->getJsonGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );
        $generator->endObjectElement( 'element' );

        $generator->startObjectElement( 'element' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testGeneratorMultipleStackedElements()
    {
        $generator = $this->getJsonGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startObjectElement( 'stacked' );
        $generator->endObjectElement( 'stacked' );

        $generator->startObjectElement( 'stacked' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidDocumentEnd()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startObjectElement( 'element' );
        $generator->endDocument( 'test' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeOuterStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startAttribute( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeDocumentStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startAttribute( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidAttributeListStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startObjectElement( 'element' );
        $generator->startList( 'list' );
        $generator->startAttribute( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementOuterStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startValueElement( 'element', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementDocumentStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startValueElement( 'element', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidValueElementListStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startObjectElement( 'element' );
        $generator->startList( 'list' );
        $generator->startValueElement( 'attribute', 'value' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListOuterStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startList( 'list' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListDocumentStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startList( 'list' );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testInvalidListListStart()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );
        $generator->startObjectElement( 'element' );
        $generator->startList( 'list' );
        $generator->startList( 'attribute', 'value' );
    }

    protected function getXmlGenerator()
    {
        if ( !isset( $this->xmlGenerator ) )
        {
            $this->xmlGenerator = new Common\Output\Generator\Xml(
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Common\\Output\\Generator\\Xml\\FieldTypeHashGenerator',
                    array(),
                    array(),
                    '',
                    false
                )
            );
        }
        return $this->xmlGenerator;
    }

    protected function getJsonGenerator()
    {
        if ( !isset( $this->jsonGenerator ) )
        {
            $this->jsonGenerator = new Common\Output\Generator\Json(
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Common\\Output\\Generator\\Json\\FieldTypeHashGenerator',
                    array(),
                    array(),
                    '',
                    false
                )
            );
        }
        return $this->jsonGenerator;
    }
}
