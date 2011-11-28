<?php
/**
 * File contains Lazy Collection interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Collection;
use ezp\Base\Collection;

/**
 * Lazy Collection interface
 *
 * Note: Does not extend IteratorAggregate / Iterator to let implementers extend ArrayObject or splFixedArray
 *
 */
interface Lazy extends Collection
{
    /**
     * Hint to know if collection has been loaded (including partly loaded)
     *
     * Useful for lazy collection to signal that a collection has not been loaded thus
     * skipping updating a collection as it will be correct the moment it is loaded anyway.
     *
     * @return bool
     */
    public function isLoaded();
}
