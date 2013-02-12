<?php
/**
 * File containing the PcreCompiler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Utf8Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor;
use RuntimeException;

/**
 * Compiles the AST of parsed transformation rules into a set of PCRE replace
 * regular expressions.
 */
class PcreCompiler
{
    /**
     * Class for converting UTF-8 characters
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Utf8Converter
     */
    protected $converter;

    /**
     * Construct from UTF8Converter
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Utf8Converter $converter
     */
    public function __construct( Utf8Converter $converter )
    {
        $this->converter = $converter;
    }

    /**
     * Compile AST into a set of regular expressions
     *
     * The returned array contains a set of regular expressions and their
     * replacement callbacks. The regular expressions can then be applied to
     * strings to executed the transformations.
     *
     * @param array $ast
     *
     * @return array
     */
    public function compile( array $ast )
    {
        $transformations = array();

        foreach ( $ast as $section => $rules )
        {
            foreach ( $rules as $rule )
            {
                $transformations[$section][] = $this->compileRule( $rule );
            }
        }

        return $transformations;
    }

    /**
     * Compiles a single rule
     *
     * @param array $rule
     *
     * @return array
     */
    protected function compileRule( array $rule )
    {
        switch ( $rule['type'] )
        {
            case TransformationProcessor::T_MAP:
                return $this->compileMap( $rule );

            case TransformationProcessor::T_REPLACE:
                return $this->compileReplace( $rule );

            case TransformationProcessor::T_TRANSPOSE:
                return $this->compileTranspose( $rule );

            case TransformationProcessor::T_TRANSPOSE_MODULO:
                return $this->compileTransposeModulo( $rule );

            default:
                throw new RuntimeException( "Unknown rule type: " . $rule['type'] );
        }
    }

    /**
     * Compile map rule
     *
     * @param array $rule
     *
     * @return array
     */
    protected function compileMap( array $rule )
    {
        return array(
            'regexp' => '(' . preg_quote( $this->compileCharacter( $rule['data']['src'] ) ) . ')us',
            'callback' => $this->compileTargetCharacter( $rule['data']['dest'] ),
        );
    }

    /**
     * Compile replace rule
     *
     * @param array $rule
     *
     * @return array
     */
    protected function compileReplace( array $rule )
    {
        return array(
            'regexp' =>
                '([' .
                preg_quote( $this->compileCharacter( $rule['data']['srcStart'] ) ) . '-' .
                preg_quote( $this->compileCharacter( $rule['data']['srcEnd'] ) ) .
                '])us',
            'callback' => $this->compileTargetCharacter( $rule['data']['dest'] ),
        );
    }

    /**
     * Compile transpose rule
     *
     * @param array $rule
     *
     * @return array
     */
    protected function compileTranspose( array $rule )
    {
        return array(
            'regexp' =>
                '([' .
                preg_quote( $this->compileCharacter( $rule['data']['srcStart'] ) ) . '-' .
                preg_quote( $this->compileCharacter( $rule['data']['srcEnd'] ) ) .
                '])us',
            'callback' => $this->getTransposeClosure( $rule['data']['op'], $rule['data']['dest'] ),
        );
    }

    /**
     * Compile transpose modulo rule
     *
     * @param array $rule
     *
     * @return array
     */
    protected function compileTransposeModulo( array $rule )
    {
        return array(
            'regexp' =>
                '([' .
                preg_quote(
                    $this->getModuloCharRange(
                        $this->compileCharacter( $rule['data']['srcStart'] ),
                        $this->compileCharacter( $rule['data']['srcEnd'] ),
                        $rule['data']['modulo']
                    )
                ) .
                '])us',
            'callback' => $this->getTransposeClosure( $rule['data']['op'], $rule['data']['dest'] ),
        );
    }

    /**
     * Get string with all characters defined by parameters
     *
     * Returns a string containing all UTF-8 characters starting with the
     * specified $start character up to the $end character with the step size
     * defined in $modulo.
     *
     * @param string $start
     * @param string $end
     * @param string $modulo
     *
     * @return string
     */
    protected function getModuloCharRange( $start, $end, $modulo )
    {
        $start = $this->converter->toUnicodeCodepoint( $start );
        $end = $this->converter->toUnicodeCodepoint( $end );
        $modulo = hexdec( $modulo );

        $chars = '';
        for ( $start; $start <= $end; $start += $modulo )
        {
            $chars .= $this->converter->toUTF8Character( $start );
        }

        return $chars;
    }

    /**
     * Returns a closure which modifies the provided character by the given
     * value
     *
     * @param string $operator
     * @param string $value
     *
     * @return callback
     */
    protected function getTransposeClosure( $operator, $value )
    {
        $value = hexdec( $value ) * ( $operator === '-' ? -1 : 1 );
        $converter = $this->converter;
        return function ( $matches ) use ( $value, $converter )
        {
            return $converter->toUTF8Character(
                $converter->toUnicodeCodepoint( $matches[0] ) + $value
            );
        };
    }

    /**
     * Compile target into a closure, which can be used by
     * preg_replace_callback
     *
     * @param string $char
     *
     * @return callback
     */
    protected function compileTargetCharacter( $char )
    {
        switch ( true )
        {
            case ( $char === 'remove' ):
                return function ( $matches )
                {
                    return '';
                };

            case ( $char === 'keep' ):
                return function ( $matches )
                {
                    return $matches[0];
                };

            case preg_match( '("(?:[^\\\\"]+|\\\\\\\\|\\\\\'|\\\\")*?")', $char );
                $string = str_replace(
                    array( '\\\\', '\\"', "\\'" ),
                    array( '\\', '"', "'" ),
                    substr( $char, 1, -1 )
                );

                return function ( $matches ) use ( $string )
                {
                    return $string;
                };

            default:
                $char = $this->compileCharacter( $char );
                return function ( $matches ) use ( $char )
                {
                    return $char;
                };
        }
    }

    /**
     * Compile a single source character definition into a plain UTF-8 character
     *
     * Handles the two formats from the possible character definitions:
     *  - U+xxxx : Unicode value in hexadecimal
     *  - xx: Ascii value in hexadecimal
     *
     * @param string $char
     *
     * @return string
     */
    protected function compileCharacter( $char )
    {
        switch ( true )
        {
            case preg_match( '(^U\\+[0-9a-fA-F]{4}$)', $char ):
                return $this->converter->toUTF8Character( hexdec( substr( $char, 2 ) ) );

            case preg_match( '(^[0-9a-fA-F]{2}$)', $char ):
                return chr( hexdec( $char ) );

            default:
                throw new RuntimeException( "Invalid character definition: $char" );
        }
    }
}

