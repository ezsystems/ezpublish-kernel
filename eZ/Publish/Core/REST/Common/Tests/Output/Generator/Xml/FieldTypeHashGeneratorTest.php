<?php
/**
 * File containing the  class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Output\Generator\Xml;

use eZ\Publish\Core\REST\Common;

class FieldTypeHashGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $xmlWriter;

    protected $fieldTypeHashGenerator;

    public function testGenerateNull()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            null
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateBoolValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            true
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateIntegerValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            23
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateFloatValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            23.424242424242424242
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateStringValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            'Sindelfingen'
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateEmptyStringValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            ''
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateStringValueWithSpecialChars()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            '<?xml version="1.0" encoding="UTF-8"?><ezxml>Sindelfingen</ezxml>'
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateListArrayValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            array(
                23,
                true,
                'Sindelfingen',
                null
            )
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateHashArrayValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            array(
                'age' => 23,
                'married' => true,
                'city' => 'Sindelfingen',
                'cause' => null
            )
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateHashArrayMixedValue()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            array(
                23,
                'married' => true,
                'Sindelfingen',
                'cause' => null
            )
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    public function testGenerateComplexValueAuthor()
    {
        $this->getFieldTypeHashGenerator()->generateHashValue(
            $this->getXmlWriter(),
            array(
                array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ),
                array( 'id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com' ),
            )
        );

        $this->assertSame(
            file_get_contents( __DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml' ),
            $this->getXmlString()
        );
    }

    protected function getFieldTypeHashGenerator()
    {
        if ( !isset( $this->fieldTypeHashGenerator ) )
        {
            $this->fieldTypeHashGenerator = new Common\Output\Generator\Xml\FieldTypeHashGenerator();
        }
        return $this->fieldTypeHashGenerator;
    }

    protected function getXmlWriter()
    {
        if ( !isset( $this->xmlWriter ) )
        {
            $this->xmlWriter = new Common\Output\Generator\Xml(
                $this->getFieldTypeHashGenerator()
            );

            $this->xmlWriter = new \XMLWriter();
            $this->xmlWriter->openMemory();
            $this->xmlWriter->setIndent( true );
            $this->xmlWriter->startDocument( '1.0', 'UTF-8' );
        }
        return $this->xmlWriter;
    }

    protected function getXmlString()
    {
        $this->getXmlWriter()->endDocument();
        return $this->getXmlWriter()->outputMemory();
    }
}
