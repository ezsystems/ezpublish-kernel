<?php
/**
 * File containing the LegacyFormulaLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating;

use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;

/**
 * This class does nothing.
 * It just avoids Assetic to bark because eztpl doesn't have a formula loader.
 */
class LegacyFormulaLoader implements FormulaLoaderInterface
{
    /**
     * Loads formulae from a resource.
     *
     * Formulae should be loaded the same regardless of the current debug
     * mode. Debug considerations should happen downstream.
     *
     * @param ResourceInterface $resource A resource
     *
     * @return array An array of formulae
     */
    public function load( ResourceInterface $resource )
    {
        return array();
    }
}
