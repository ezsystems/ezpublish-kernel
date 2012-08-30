<?php
/**
 * File containing the XmlTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Output\Generator;
use eZ\Publish\Core\REST\Common\Tests\Output\GeneratorTest;

use eZ\Publish\Core\REST\Common;

require_once __DIR__ . '/../GeneratorTest.php';

/**
 * Xml generator test class
 */
class XmlTest extends GeneratorTest
{
    public function testGeneratorDocument()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElement()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );
        $generator->endObjectElement( 'element' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElementMediaTypeOverwrite()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element', 'User' );
        $generator->endObjectElement( 'element' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorStackedElement()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startObjectElement( 'stacked' );
        $generator->endObjectElement( 'stacked' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorAttribute()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startAttribute( 'attribute', 'value' );
        $generator->endAttribute( 'attribute' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorMultipleAttributes()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startAttribute( 'attribute1', 'value' );
        $generator->endAttribute( 'attribute1' );

        $generator->startAttribute( 'attribute2', 'value' );
        $generator->endAttribute( 'attribute2' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorValueElement()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startObjectElement( 'element' );

        $generator->startValueElement( 'value', '42' );
        $generator->endValueElement( 'value' );

        $generator->endObjectElement( 'element' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorElementList()
    {
        $generator = $this->getXmlGenerator();

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
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGeneratorHashElement()
    {
        $generator = $this->getXmlGenerator();

        $generator->startDocument( 'test' );

        $generator->startHashElement( 'elements' );

        $generator->startHashValueElement( 'element', 'element value 1', array( 'attribute' => 'attribute value 1' ) );
        $generator->endHashValueElement( 'element' );

        $generator->startHashValueElement( 'element', 'element value 2', array( 'attribute' => 'attribute value 2' ) );
        $generator->endHashValueElement( 'element' );

        $generator->endHashElement( 'elements' );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $generator->endDocument( 'test' )
        );
    }

    public function testGetMediaType()
    {
        $generator = $this->getXmlGenerator();

        $this->assertEquals(
            'application/vnd.ez.api.Section+xml',
            $generator->getMediaType( 'Section' )
        );
    }
}
