<?php
/**
 * File containing the JsonTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Output\Generator;

use eZ\Publish\Core\REST\Common\Tests\Output\GeneratorTest;

use eZ\Publish\Core\REST\Common;

require_once __DIR__ . '/../GeneratorTest.php';

/**
 * Json output generator test class
 */
class JsonTest extends GeneratorTest
{
    protected $generator;

    public function testGeneratorDocument()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $this->assertSame(
            '{}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElement()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );
        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElementMediaTypeOverwrite()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element', 'User' );
        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.User+json"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorStackedElement()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startObjectElement( 'stacked' );
        $generator->endObjectElement( 'stacked' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","stacked":{"_media-type":"application\/vnd.ez.api.stacked+json"}}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorAttribute()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startAttribute( 'attribute', 'value' );
        $generator->endAttribute( 'attribute' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","_attribute":"value"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorMultipleAttributes()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startAttribute( 'attribute1', 'value' );
        $generator->endAttribute( 'attribute1' );

        $generator->startAttribute( 'attribute2', 'value' );
        $generator->endAttribute( 'attribute2' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","_attribute1":"value","_attribute2":"value"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorValueElement()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startValueElement( 'value', '42' );
        $generator->endValueElement( 'value' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","value":"42"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElementList()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'elementList' );

        $generator->startList( 'elements' );

        $generator->startObjectElement( 'element' );
        $generator->endObjectElement( 'element' );

        $generator->startObjectElement( 'element' );
        $generator->endObjectElement( 'element' );

        $generator->endList( 'elements' );

        $generator->endObjectElement( 'elementList' );

        $this->assertSame(
            '{"elementList":{"_media-type":"application\/vnd.ez.api.elementList+json","elements":[{"_media-type":"application\/vnd.ez.api.element+json"},{"_media-type":"application\/vnd.ez.api.element+json"}]}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorHashElement()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startHashElement( 'elements' );

        $generator->startValueElement( 'element', 'element value 1', array( 'attribute' => 'attribute value 1' ) );
        $generator->endValueElement( 'element' );

        $generator->endHashElement( 'elements' );

        $this->assertSame(
            '{"elements":{"element":{"_attribute":"attribute value 1","#text":"element value 1"}}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorValueList()
    {
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );
        $generator->startObjectElement( 'element' );
        $generator->startList( 'simpleValue' );

        $generator->startValueElement( 'simpleValue', 'value1' );
        $generator->endValueElement( 'simpleValue' );
        $generator->startValueElement( 'simpleValue', 'value2' );
        $generator->endValueElement( 'simpleValue' );

        $generator->endList( 'simpleValue' );
        $generator->endObjectElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","simpleValue":["value1","value2"]}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGetMediaType()
    {
        $generator = $this->getGenerator();

        $this->assertEquals(
            'application/vnd.ez.api.Section+json',
            $generator->getMediaType( 'Section' )
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\OutputGeneratorException
     */
    public function testGeneratorMultipleElements()
    {
        $generator = $this->getGenerator();

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
        $generator = $this->getGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startObjectElement( 'stacked' );
        $generator->endObjectElement( 'stacked' );

        $generator->startObjectElement( 'stacked' );
    }

    protected function getGenerator()
    {
        if ( !isset( $this->generator ) )
        {
            $this->generator = new Common\Output\Generator\Json(
                $this->getMock(
                    'eZ\\Publish\\Core\\REST\\Common\\Output\\Generator\\Json\\FieldTypeHashGenerator',
                    array(),
                    array(),
                    '',
                    false
                )
            );
        }
        return $this->generator;
    }
}
