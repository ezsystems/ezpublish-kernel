<?php
/**
 * File containing the TransformationProcessor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search;

/**
 * Class for processing a set of transformations on a string
 */
class TransformationProcessor
{
    /**
     * Transformation parser
     *
     * @var TransformationParser
     */
    protected $parser = null;

    /**
     * Transformation compiler
     *
     * @var TransformationPcreCompiler
     */
    protected $compiler = null;

    /**
     * Parsed rules
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Compiled rules, which can directly be applied to the input strings
     *
     * @var array
     */
    protected $compiledRules = null;

    /**
     * Construct
     *
     * @return void
     */
    public function __construct(
        TransformationParser $parser,
        TransformationPcreCompiler $compiler,
        array $rules = array()
    )
    {
        $this->parser = $parser;
        $this->compiler = $compiler;
        foreach( $rules as $file )
        {
            $this->loadRules( $file );
        }
    }

    /**
     * Load rules from the given file
     *
     * @param string $file
     * @return void
     */
    public function loadRules( $file )
    {
        $this->rules = array_merge(
            $this->rules,
            $this->parser->parse( $file )
        );
        $this->compiledRules = null;
    }

    /**
     * Transform the given string
     *
     * Transform the given string using the given rules. If no rules are
     * specified, all available rules will be used for the transformation.
     *
     * @param string $string
     * @param array $ruleNames
     * @return string
     */
    public function transform( $string, array $ruleNames = null )
    {
        if ( $this->compiledRules === null )
        {
            $this->compiledRules = $this->compiler->compile( $this->rules );
        }

        $ruleNames = $ruleNames ?: array_keys( $this->compiledRules );

        foreach ( $ruleNames as $ruleName )
        {
            if ( !isset( $this->compiledRules[$ruleName] ) )
            {
                // Just continue on unknow rules, or should we throw an error
                // here?
                continue;
            }

            foreach ( $this->compiledRules[$ruleName] as $rule )
            {
                $string = preg_replace_callback(
                    $rule['regexp'],
                    $rule['callback'],
                    $string
                );
            }
        }

        return $string;
    }
}

