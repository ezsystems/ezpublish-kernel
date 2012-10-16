<?php
/**
 * File containing the TransformationProcessor\DefinitionBased class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\PcreCompiler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\DefinitionBased\Parser;

/**
 * Class for processing a set of transformations, loaded from .tr files, on a string
 */
class DefinitionBased extends TransformationProcessor
{
    /**
     * Transformation parser
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\DefinitionBased\Parser
     */
    protected $parser = null;

    /**
     * Construct instance of TransformationProcessor\DefinitionBased
     *
     * Through the $ruleFiles array, a list of files with full text
     * transformation rules is given. These files are parsed by
     * {@link \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\DefinitionBased\Parser}
     * and then used for normalization in the full text search.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\DefinitionBased\Parser $parser
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\PcreCompiler $compiler
     * @param array $ruleFiles
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\DefinitionBased
     */
    public function __construct( Parser $parser, PcreCompiler $compiler, array $ruleFiles = array() )
    {
        parent::__construct( $compiler, $ruleFiles );
        $this->parser = $parser;
    }

    /**
     * Load rules
     *
     * @return array
     */
    protected function getRules()
    {
        if ( $this->compiledRules === null )
        {
            $rules = array();
            foreach ( $this->ruleFiles as $file )
            {
                $rules = array_merge(
                    $rules,
                    $this->parser->parse( $file )
                );
            }

            $this->compiledRules = $this->compiler->compile( $rules );
        }

        return $this->compiledRules;
    }
}
