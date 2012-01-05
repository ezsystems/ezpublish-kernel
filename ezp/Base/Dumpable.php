<?php
/**
 * File containing the ezp\Base\Dumpable interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use SplObjectStorage;

/**
 * Interface for content that can be rendered using the dump method.
 */
interface Dumpable
{
    /**
     * Default dump depth
     *
     * @var int
     */
    const DEFAULT_DEPTH = 5;

    /**
     * Dump an object in a similar way to var_dump()
     *
     * @param int $maxDepth Maximum depth
     * @param int $currentLevel Current level
     * @param \SplObjectStorage Set of objects already printed (to avoid recursion)
     */
    public function dump( $maxDepth = self::DEFAULT_DEPTH, $currentLevel = 0, SplObjectStorage $objectSet = null );
}
