<?php

/**
 * File containing the TransformationProcessor\PreprocessedBased class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\TransformationProcessor;

use eZ\Publish\Core\Persistence\TransformationProcessor;

/**
 * Class for processing a set of transformations, loaded from .tr files, on a string.
 */
class PreprocessedBased extends TransformationProcessor
{
    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor\PcreCompiler $compiler
     * @param string $installDir Base dir for rule loading
     * @param array $ruleFiles
     */
    public function __construct(PcreCompiler $compiler, array $ruleFiles = [])
    {
        parent::__construct($compiler, $ruleFiles);
    }

    /**
     * Loads rules.
     *
     * @return array
     */
    protected function getRules()
    {
        if ($this->compiledRules === null) {
            $rules = [];

            foreach ($this->ruleFiles as $file) {
                $rules += require $file;
            }

            $this->compiledRules = $this->compiler->compile($rules);
        }

        return $this->compiledRules;
    }
}
