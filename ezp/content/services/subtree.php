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
use ezp\Content\Repository as ContentRepository;

class Subtree implements ezp\Service
{
    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * @param \ezp\Content\Location $subtree
     * @param \ezp\Content\Location $targetLocation
     *
     * @return \ezp\Content\Location The newly created subtree
     */
    public function copy( \ezp\Content\Location $subtree, \ezp\Content\Location $targetLocation )
    {
        // is there any point in having a service at all, as the copy is to be performed directly by the storage
        // engine in order to be as optimized as possible ?
        return ContentRepository::get()->copySubtree( $subtree, $targetLocation );
    }
}
?>