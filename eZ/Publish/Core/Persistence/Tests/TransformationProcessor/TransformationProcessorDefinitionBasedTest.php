<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandler\TransformationProcessorDefinitionBasedTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Tests\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Persistence\TransformationProcessor\DefinitionBased;

/**
 * Test case for LocationHandlerTest
 */
class TransformationProcessorDefinitionBasedTest extends TestCase
{
    public function getProcessor()
    {
        $rules = array();
        foreach ( glob( __DIR__ . '/_fixtures/transformations/*.tr' ) as $file )
        {
            $rules[] = str_replace( self::getInstallationDir(), '', $file );
        }

        return new DefinitionBased(
            new Persistence\TransformationProcessor\DefinitionBased\Parser( self::getInstallationDir() ),
            new Persistence\TransformationProcessor\PcreCompiler( new Persistence\Utf8Converter() ),
            $rules
        );
    }

    public function testSimpleNormalizationLowercase()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'hello world!',
            $processor->transform( 'Hello World!', array( 'ascii_lowercase' ) )
        );
    }

    public function testSimpleNormalizationUppercase()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'HELLO WORLD!',
            $processor->transform( 'Hello World!', array( 'ascii_uppercase' ) )
        );
    }

    public function testApplyAllLowercaseNormalizations()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'hello world!',
            $processor->transformByGroup( 'Hello World!', 'lowercase' )
        );
    }

    /**
     * The main point of this test is, that it shows that all normalizations
     * available can be compiled without errors. The actual expectation is not
     * important.
     */
    public function testAllNormalizations()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'HELLO WORLD.',
            $processor->transform( 'Hello World!' )
        );
    }
}

