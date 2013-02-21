<?php
/**
 * File containing the TransformationProcessor\PreprocessedBased class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor;

/**
 * Class for processing a set of transformations, loaded from .tr files, on a string
 */
class PreprocessedBased extends TransformationProcessor
{
    /**
     * Directory to load rules relative from.
     *
     * @var string
     */
    protected $installDir;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor\PcreCompiler $compiler
     * @param string $installDir Base dir for rule loading
     * @param array $ruleFiles
     */
    public function __construct( PcreCompiler $compiler, $installDir, array $ruleFiles = array() )
    {
        parent::__construct( $compiler, $ruleFiles );
        $this->installDir = $installDir;
    }

    /**
     * Loads rules
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
                $rules += require $this->installDir . "/" . $file;
            }

            $this->compiledRules = $this->compiler->compile( $rules );
        }

        return $this->compiledRules;
    }
}
