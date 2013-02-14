<?php
/**
 * File containing the  class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Output\Generator;

use eZ\Publish\Core\REST\Common;

abstract class FieldTypeHashGeneratorBaseTest extends \PHPUnit_Framework_TestCase
{
    private $generator;

    private $fieldTypeHashGenerator;

    /**
     * Initializes the field type hash generator
     */
    abstract protected function initializeFieldTypeHashGenerator();

    /**
     * Initializes the generator
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator
     */
    abstract protected function initializeGenerator();

    public function testGenerateNull()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            null
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateBoolValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            true
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateIntegerValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            23
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateFloatValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            23.424242424242424242
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateStringValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            'Sindelfingen'
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateEmptyStringValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            ''
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateStringValueWithSpecialChars()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            '<?xml version="1.0" encoding="UTF-8"?><ezxml>Sindelfingen</ezxml>'
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateListArrayValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            array(
                23,
                true,
                'Sindelfingen',
                null
            )
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateHashArrayValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            array(
                'age' => 23,
                'married' => true,
                'city' => 'Sindelfingen',
                'cause' => null
            )
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateHashArrayMixedValue()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            array(
                23,
                'married' => true,
                'Sindelfingen',
                'cause' => null
            )
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    public function testGenerateComplexValueAuthor()
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            array(
                array( 'id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com' ),
                array( 'id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com' ),
            )
        );

        $this->assertSerializationSame( __FUNCTION__ );
    }

    protected function getFieldTypeHashGenerator()
    {
        if ( !isset( $this->fieldTypeHashGenerator ) )
        {
            $this->fieldTypeHashGenerator = $this->initializeFieldTypeHashGenerator();
        }
        return $this->fieldTypeHashGenerator;
    }

    protected function getGenerator()
    {
        if ( !isset( $this->generator ) )
        {
            $this->generator = $this->initializeGenerator();
            $this->generator->startDocument( 'Version' );
            $this->generator->startHashElement( 'Field' );
        }
        return $this->generator;
    }

    private function getGeneratorOutput()
    {
        $this->getGenerator()->endHashElement( 'Field' );
        return $this->getGenerator()->endDocument( 'Version' );
    }

    private function assertSerializationSame( $functionName )
    {
        $fixtureFile = $this->getFixtureFile( $functionName );
        $actualResult = $this->getGeneratorOutput();

        // file_put_contents( $fixtureFile, $actualResult );
        // $this->markTestIncomplete( "Wrote fixture to '{$fixtureFile}'." );

        $this->assertSame(
            file_get_contents( $this->getFixtureFile( $functionName ) ),
            $actualResult
        );
    }

    private function getFixtureFile( $functionName )
    {
        return sprintf(
            '%s/_fixtures/%s__%s.out',
            __DIR__,
            $this->getRelativeClassIdentifier(),
            $functionName
        );
    }

    private function getRelativeClassIdentifier()
    {
        $fqClassName = get_called_class();

        return strtr(
            substr(
                $fqClassName,
                strlen( __NAMESPACE__ ) + 1
            ),
            array( '\\' => '_' )
        );
    }
}
