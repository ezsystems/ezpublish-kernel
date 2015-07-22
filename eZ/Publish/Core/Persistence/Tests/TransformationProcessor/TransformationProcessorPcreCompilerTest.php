<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandler/TransformationProcessorPcreCompilerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Tests\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence;

/**
 * Test case for LocationHandlerTest.
 */
class TransformationProcessorPcreCompilerTest extends TestCase
{
    /**
     * Applies the transformations.
     *
     * @param array $transformations
     * @param string $string
     *
     * @return string
     */
    protected function applyTransformations(array $transformations, $string)
    {
        foreach ($transformations as $rules) {
            foreach ($rules as $rule) {
                $string = preg_replace_callback($rule['regexp'], $rule['callback'], $string);
            }
        }

        return $string;
    }

    public function testCompileMap()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                'U+00e4 = "ae"'
            )
        );

        $this->assertSame(
            'aeöü',
            $this->applyTransformations($rules, 'äöü')
        );
    }

    public function testCompileMapRemove()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                'U+00e4 = remove'
            )
        );

        $this->assertSame(
            'öü',
            $this->applyTransformations($rules, 'äöü')
        );
    }

    public function testCompileMapKeep()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                'U+00e4 = keep'
            )
        );

        $this->assertSame(
            'äöü',
            $this->applyTransformations($rules, 'äöü')
        );
    }

    public function testCompileMapAscii()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                'U+00e4 = 41'
            )
        );

        $this->assertSame(
            'Aöü',
            $this->applyTransformations($rules, 'äöü')
        );
    }

    public function testCompileMapUnicode()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                'U+00e4 = U+00e5'
            )
        );

        $this->assertSame(
            'åöü',
            $this->applyTransformations($rules, 'äöü')
        );
    }

    public function testCompileReplace()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "replace_test:\n" .
                'U+00e0 - U+00e6 = "a"'
            )
        );

        $this->assertSame(
            'aaaaaaaçè',
            $this->applyTransformations($rules, 'àáâãäåæçè')
        );
    }

    public function testCompileTranspose()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "transpose_test:\n" .
                'U+00e0 - U+00e6 - 02'
            )
        );

        $this->assertSame(
            'Þßàáâãäçè',
            $this->applyTransformations($rules, 'àáâãäåæçè')
        );
    }

    public function testCompileTransposeAsciiLowercase()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "ascii_lowercase:\n" .
                'U+0041 - U+005A + 20'
            )
        );

        $this->assertSame(
            'hello world',
            $this->applyTransformations($rules, 'Hello World')
        );
    }

    public function testCompileTransposePlus()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "transpose_test:\n" .
                'U+00e0 - U+00e6 + 02'
            )
        );

        $this->assertSame(
            'âãäåæçèçè',
            $this->applyTransformations($rules, 'àáâãäåæçè')
        );
    }

    public function testCompileModuloTranspose()
    {
        $parser = new Persistence\TransformationProcessor\DefinitionBased\Parser(self::getInstallationDir());
        $compiler = new Persistence\TransformationProcessor\PcreCompiler(new Persistence\Utf8Converter());

        $rules = $compiler->compile(
            $parser->parseString(
                "transpose_modulo_test:\n" .
                'U+00e0 - U+00e6 % 02 - 01'
            )
        );

        $this->assertSame(
            'ßááããååçè',
            $this->applyTransformations($rules, 'àáâãäåæçè')
        );
    }
}
