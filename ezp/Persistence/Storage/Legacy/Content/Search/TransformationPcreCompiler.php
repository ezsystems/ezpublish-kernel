<?php
/**
 * File containing the TransformationPcreCompiler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search;
use ezp\Persistence\Fields\Storage;

/**
 * Compiles the AST of parsed transformation rules into a set of PCRE replace
 * regular expressions.
 */
class TransformationPcreCompiler
{
    /**
     * Compile AST into a set of regular expressions
     *
     * The returned array contains a set of regular expressions and their
     * replacement callbacks. The regular expressions can then be applied to
     * strings to executed the transformations.
     *
     * @param array $ast
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
     * @return array
     */
    protected function compileRule( array $rule )
    {
        switch ( $rule['type'] )
        {
            case TransformationParser::T_MAP:
                return $this->compileMap( $rule );

            case TransformationParser::T_REPLACE:
                return $this->compileReplace( $rule );

            case TransformationParser::T_TRANSPOSE:
                return $this->compileTranspose( $rule );

            case TransformationParser::T_TRANSPOSE_MODULO:
                return $this->compileTransposeModulo( $rule );

            default:
                throw new \RuntimeException( "Unknown rule type: " . $rule['type'] );
        }
    }

    /**
     * Compile map rule
     *
     * @param array $rule
     * @return array
     */
    protected function compileMap( array $rule )
    {
        return array(
            'regexp'   => '(' . $this->compileCharacter( $rule['data']['src'] ) . ')us',
            'callback' => $this->compileTargetCharacter( $rule['data']['dest'] ),
        );
    }

    /**
     * Compile replace rule
     *
     * @param array $rule
     * @return array
     */
    protected function compileReplace( array $rule )
    {
        return array(
            'regexp'   => '([' .
                $this->compileCharacter( $rule['data']['srcStart'] ) . '-' .
                $this->compileCharacter( $rule['data']['srcEnd'] ) .
                '])us',
            'callback' => $this->compileTargetCharacter( $rule['data']['dest'] ),
        );
    }

    /**
     * Compile target into a closure, which can be used by
     * preg_replace_callback
     *
     * @param string $char
     * @return callback
     */
    protected function compileTargetCharacter( $char )
    {
        switch ( true )
        {
            case ( $char === 'remove' ):
                return function( $matches )
                {
                    return '';
                };

            case ( $char === 'keep' ):
                return function( $matches )
                {
                    return $matches[0];
                };

            case preg_match( '("(?:[^\\\\"]+|\\\\\\\\|\\\\\'|\\\\")*?")', $char );
                $string = str_replace(
                    array( '\\\\', '\\"', "\\'" ),
                    array( '\\', '"', "'" ),
                    substr( $char, 1, -1 )
                );

                return function( $matches ) use ( $string )
                {
                    return $string;
                };

            default:
                $char = $this->compileCharacter( $char );
                return function( $matches ) use ( $char )
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
     * @return string
     */
    protected function compileCharacter( $char )
    {
        switch ( true )
        {
            case preg_match( '(^U\\+[0-9a-fA-F]{4}$)', $char ):
                return $this->compileUnicodeCharacter( substr( $char, 2 ) );

            case preg_match( '(^[0-9a-fA-F]{2}$)', $char ):
                return chr( hexdec( $char ) );

            default:
                throw new \RuntimeException( "Invalid character definition: $char" );
        }
    }

    /**
     * Ompile hexadecimal unicode character definition into a UTF-8 character
     *
     * @param string $char
     * @return string
     */
    protected function compileUnicodeCharacter( $char )
    {
        return html_entity_decode( '&#x' . $char . ';', ENT_QUOTES, 'UTF-8' );
    }
}

