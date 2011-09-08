<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\SearchHandler/TransformationParserTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\SearchHandler;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Search;

/**
 * Test case for LocationHandlerTest
 */
class TransformationPcreCompilerTest extends TestCase
{
    /**
     * Applies the transformations
     *
     * @param array $transformations
     * @param string $string
     * @return string
     */
    protected function applyTransformations( array $transformations, $string )
    {
        foreach ( $transformations as $rules )
        {
            foreach ( $rules as $rule )
            {
                $string = preg_replace_callback( $rule['src'], $rule['dest'], $string );
            }
        }

        return $string;
    }

    public function testCompileMap()
    {
        $parser   = new Search\TransformationParser();
        $compiler = new Search\TransformationPcreCompiler();

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                "U+00e4 = \"ae\""
            )
        );

        $this->assertSame(
            'aeöü',
            $this->applyTransformations( $rules, 'äöü' )
        );
    }

    public function testCompileMapRemove()
    {
        $parser   = new Search\TransformationParser();
        $compiler = new Search\TransformationPcreCompiler();

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                "U+00e4 = remove"
            )
        );

        $this->assertSame(
            'öü',
            $this->applyTransformations( $rules, 'äöü' )
        );
    }

    public function testCompileMapKeep()
    {
        $parser   = new Search\TransformationParser();
        $compiler = new Search\TransformationPcreCompiler();

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                "U+00e4 = keep"
            )
        );

        $this->assertSame(
            'äöü',
            $this->applyTransformations( $rules, 'äöü' )
        );
    }

    public function testCompileMapAscii()
    {
        $parser   = new Search\TransformationParser();
        $compiler = new Search\TransformationPcreCompiler();

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                "U+00e4 = 41"
            )
        );

        $this->assertSame(
            'Aöü',
            $this->applyTransformations( $rules, 'äöü' )
        );
    }

    public function testCompileMapUnicode()
    {
        $parser   = new Search\TransformationParser();
        $compiler = new Search\TransformationPcreCompiler();

        $rules = $compiler->compile(
            $parser->parseString(
                "map_test:\n" .
                "U+00e4 = U+00e5"
            )
        );

        $this->assertSame(
            'åöü',
            $this->applyTransformations( $rules, 'äöü' )
        );
    }
}

