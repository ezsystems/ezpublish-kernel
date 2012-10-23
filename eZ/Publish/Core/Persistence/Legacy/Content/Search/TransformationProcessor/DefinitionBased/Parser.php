<?php
/**
 * File containing the Transformation Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\DefinitionBased;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    RuntimeException;

/**
 * Parser for transformation specifications
 *
 * The transformation specifications look like:
 *
 *  CF = CF...           : Map from one char to one or more chars  (map)
 *  CF - CF = CF...      : Map range of chars to one or more chars (replace)
 *  CF - CF +- xx        : Transpose several chars by value xx     (transpose)
 *  CF - CF % yy +- xx   : Transpose several chars by value xx, yy denotes skip value
 *                         yy equal to 1 is the same as 'transpose' (transpose-modulo)
 *  TI[,TI...]           :
 *
 *  CF = Character Format
 *  TI = Transform Identifier
 *
 *  Character formats:
 *  U+xxxx : Unicode value in hexadecimal
 *  xx: Ascii value in hexadecimal
 *  remove : Remove character from result, can only be used in destination
 *  keep : Keep character as it is, can only be used in destination
 *  "xxxx" : Multiple characters as a string, can only be used in destination, \\ means \ and \" means "
 */
class Parser
{
    /**
     * Array of token specifications.
     *
     * For readability reasons this array is created in the constructor to be
     * able to use temporary variables.
     *
     * @var array
     */
    protected $tokenSpecifications = null;

    /**
     * Directory to load rules relative from.
     *
     * @var string
     */
    protected $installDir;

    /**
     * Construct
     *
     * @param string $installDir Base dir for rule loading
     * @return void
     */
    public function __construct( $installDir )
    {
        $this->installDir = $installDir;

        $character = '(?:U\\+[0-9a-fA-F]{4}|remove|keep|[0-9a-fA-F]+|"(?:[^\\\\"]+|\\\\\\\\|\\\\\'|\\\\")*?")';

        $this->tokenSpecifications = array(
            TransformationProcessor::T_COMMENT => '(\\A#(?P<comment>.*)$)m',
            TransformationProcessor::T_WHITESPACE => '(\\A\\s+)',
            TransformationProcessor::T_SECTION => '(\\A(?P<section>[a-z0-9_-]+):\s*$)m',
            TransformationProcessor::T_MAP => '(\\A(?P<src>' . $character . ')\\s*=\\s*(?P<dest>' .  $character . '))',
            TransformationProcessor::T_REPLACE => '(\\A(?P<srcStart>' . $character . ')\\s*-\\s*' .
                '(?P<srcEnd>'   . $character . ')\\s*=\\s*' .
                '(?P<dest>'    .  $character . '))',
            TransformationProcessor::T_TRANSPOSE => '(\\A(?P<srcStart>' . $character . ')\\s*-\\s*' .
                '(?P<srcEnd>'   . $character . ')\\s*' .
                '(?P<op>[+-])\\s*' .
                '(?P<dest>' .     $character . '))',
            TransformationProcessor::T_TRANSPOSE_MODULO => '(\\A(?P<srcStart>' . $character . ')\\s*-\\s*' .
                '(?P<srcEnd>'   . $character . ')\\s*%\\s*' .
                '(?P<modulo>'   . $character . ')\\s*' .
                '(?P<op>[+-])\\s*' .
                '(?P<dest>' . $character . '))',
        );
    }

    /**
     * Parse the specified transformation file into an AST
     *
     * @param string $file
     * @return array
     */
    public function parse( $file )
    {
        return $this->parseString( file_get_contents(
            $this->installDir . '/' . $file
        ) );
    }

    /**
     * Parse the given string into an AST
     *
     * @param string $string
     * @return array
     */
    public function parseString( $string )
    {
        $tokens = $this->tokenize( $string );

        $tokens = array_filter(
            $tokens,
            function ( $token )
            {
                return !( $token['type'] === TransformationProcessor::T_WHITESPACE ||
                          $token['type'] === TransformationProcessor::T_COMMENT );
            }
        );

        $ast = array();
        $section = null;
        while ( $token = array_shift( $tokens ) )
        {
            if ( $token['type'] === TransformationProcessor::T_SECTION )
            {
                $section = $token['data']['section'];
                continue;
            }

            if ( $section === null )
            {
                throw new RuntimeException( "Expected section." );
            }

            $ast[$section][] = $token;
        }

        return $ast;
    }

    /**
     * Tokenize transformation input file
     *
     * Returns an array of tokens
     *
     * @param string $string
     * @return array
     */
    protected function tokenize( $string )
    {
        $string = preg_replace( '(\\r\\n|\\r)', "\n", $string );
        $tokens = array();
        $line = 1;

        while ( strlen( $string ) )
        {
            foreach ( $this->tokenSpecifications as $token => $regexp )
            {
                if ( !preg_match( $regexp, $string, $matches ) )
                {
                    continue;
                }

                // Remove matched string
                $string = substr( $string, strlen( $matches[0] ) );
                $line += substr_count( $matches[0], "\n" );

                // Append token to list
                $tokens[] = array(
                    'type' => $token,
                    'data' => $this->filterValues( $matches ),
                );

                // Continue with outer loop
                continue 2;
            }

            throw new RuntimeException( "Parse error in line $line: " . substr( $string, 0, 100 ) );
        }

        return $tokens;
    }

    /**
     * Filter out numeric array keys
     *
     * @param array $data
     * @return array
     */
    protected function filterValues( array $data )
    {
        foreach ( $data as $key => $value )
        {
            if ( is_int( $key ) )
            {
                unset( $data[$key] );
            }
        }

        return $data;
    }
}

