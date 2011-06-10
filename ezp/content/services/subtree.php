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

class Subtree implements ServiceInterface
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
    }

    /**
     * Loads a location object from its $id
     * @param integer $id
     * @return \ezp\Content\Location
     * @throws \ezp\Content\LocationNotFoundException if no location is available with $id
     */
    public function load( $locationId )
    {

    }


    public function children( \ezp\Content\Location $location )
    {

    }

    public function create( \ezp\Content\Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    public function update( \ezp\Content\Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    public function swap( \ezp\Content\Location $location1, \ezp\Content\Location $location2 )
    {

    }

    public function hide( \ezp\Content\Location $location )
    {
        // take care of :
        // 1. hidding $location
        // 2. making the whole subtree invisible
    }

    public function unhide( \ezp\Content\Location $location )
    {
        // take care of :
        // 1. unhidding $location
        // 2. making the whole subtree visible (unless we found a hidden
        // location)
    }

    public function move( \ezp\Content\Location $location, \ezp\Content\Location $newParent )
    {
        // take care of :
        // 1. set parentId and path for $location
        // 2. changing path attribute to the subtree below $location
    }


    public function delete( \ezp\Content\Location $location )
    {
        // take care of:
        // 1. removing the current location
        // 2. removing the content addressed by the location if there's no more
        // location
        // 3. do the same operations on the subtree (recursive calls through
        // children ?)
        // note: this is different from Content::delete()
    }
}
?>
