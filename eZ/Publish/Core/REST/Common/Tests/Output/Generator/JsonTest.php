<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Tests\Output\Generator;
use eZ\Publish\API\REST\Common\Tests\Output\GeneratorTest;

use eZ\Publish\API\REST\Common;

require_once __DIR__ . '/../GeneratorTest.php';

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 */
class JsonTest extends GeneratorTest
{
    public function testGeneratorDocument()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $this->assertSame(
            '{}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElement()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );
        $generator->endElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElementMediaTypeOverwrite()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element', 'User' );
        $generator->endElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.User+json"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorStackedElement()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );

        $generator->startElement( 'stacked' );
        $generator->endElement( 'stacked' );

        $generator->endElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","stacked":{"_media-type":"application\/vnd.ez.api.stacked+json"}}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorAttribute()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );

        $generator->startAttribute( 'attribute', 'value' );
        $generator->endAttribute( 'attribute' );

        $generator->endElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","_attribute":"value"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorMultipleAttributes()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );

        $generator->startAttribute( 'attribute1', 'value' );
        $generator->endAttribute( 'attribute1' );

        $generator->startAttribute( 'attribute2', 'value' );
        $generator->endAttribute( 'attribute2' );

        $generator->endElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","_attribute1":"value","_attribute2":"value"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorValueElement()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'element' );

        $generator->startValueElement( 'value', '42' );
        $generator->endValueElement( 'value' );

        $generator->endElement( 'element' );

        $this->assertSame(
            '{"element":{"_media-type":"application\/vnd.ez.api.element+json","value":"42"}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElementList()
    {
        $generator = new Common\Output\Generator\Json();

        $generator->startDocument( 'test' );

        $generator->startElement( 'elementList' );

        $generator->startList( 'elements' );

        $generator->startElement( 'element' );
        $generator->endElement( 'element' );

        $generator->startElement( 'element' );
        $generator->endElement( 'element' );

        $generator->endList( 'elements' );

        $generator->endElement( 'elementList' );

        $this->assertSame(
            '{"elementList":{"_media-type":"application\/vnd.ez.api.elementList+json","elements":[{"_media-type":"application\/vnd.ez.api.element+json"},{"_media-type":"application\/vnd.ez.api.element+json"}]}}',
            $generator->endDocument( 'test' )
        );
    }

    public function testGetMediaType()
    {
        $generator = new Common\Output\Generator\Json();

        $this->assertEquals(
            'application/vnd.ez.api.Section+json',
            $generator->getMediaType( 'Section' )
        );
    }
}

