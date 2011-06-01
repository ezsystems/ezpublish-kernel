<?php
/**
 * File containing the ezp\Content\Services\Subtree class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package Content
 * @subpackages Services
 */

/**
 * Subtree service, used for complex subtree operations
 * @package Content
 * @subpackage Services
 */
namespace ezp\Content\Services;

class Subtree implements ezp\Service
{
    /**
     * Copies the subtree starting from $subtree to $targetLocation
     *
     * @param \ezp\Content\Location $subtree
     * @param \ezp\Content\Location $targetLocation
     */
    public function copy( \ezp\Content\Location $subtree, \ezp\Content\Location $targetLocation )
    {

    }
}
?>