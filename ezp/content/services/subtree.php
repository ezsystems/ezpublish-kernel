<?php
/**
 * File containing the ezp\content\Services\Subtree class.
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
namespace ezp\content\Services;
use ezp\content\Repository as ContentRepository;

class Subtree implements ServiceInterface
{
    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * @param \ezp\content\Location $subtree
     * @param \ezp\content\Location $targetLocation
     *
     * @return \ezp\content\Location The newly created subtree
     */
    public function copy( \ezp\content\Location $subtree, \ezp\content\Location $targetLocation )
    {
    }

    /**
     * Loads a location object from its $locationId
     * @param integer $locationId
     * @return \ezp\content\Location
     * @throws \ezp\content\LocationNotFoundException if no location is available with $locationId
     */
    public function load( $locationId )
    {

    }


    public function children( \ezp\content\Location $location )
    {

    }

    /**
     * Creates the new $location in the content repository
     * 
     * @param \ezp\content\Location $location
     * @return \ezp\content\Location the newly created Location
     * @throws \ezp\content\ValidationException If a validation problem has been found for $content
     */
    public function create( \ezp\content\Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    /**
     * Updates $location in the content repository
     * 
     * @param \ezp\content\Location $location
     * @return \ezp\content\Location the updated Location
     * @throws \ezp\content\ValidationException If a validation problem has been found for $content
     */
    public function update( \ezp\content\Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    /**
     * Swaps the contents hold by the $location1 and $location2
     * 
     * @param \ezp\content\Location $location1
     * @param \ezp\content\Location $location2
     * @return void
     * @throws \ezp\content\ValidationException If a validation problem has been found
     */
    public function swap( \ezp\content\Location $location1, \ezp\content\Location $location2 )
    {

    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     * 
     * @param \ezp\content\Location $location
     * @return void
     * @throws \ezp\content\ValidationException If a validation problem has been found
     */
    public function hide( \ezp\content\Location $location )
    {
        // take care of :
        // 1. hidding $location
        // 2. making the whole subtree invisible
    }

    /**
     * Unhides the $location and marks visible all descendants of $locations
     * until a hidden location is found. 
     * 
     * @param \ezp\content\Location $location
     * @return void
     * @throws \ezp\content\ValidationException If a validation problem has been found;
     */
    public function unhide( \ezp\content\Location $location )
    {
        // take care of :
        // 1. unhidding $location
        // 2. making the whole subtree visible (unless we found a hidden
        // location)
    }

    /**
     * Moves $location under $newParent and updates all descendants of
     * $location accordingly. 
     * 
     * @param \ezp\content\Location $location
     * @param \ezp\content\Location $newParent
     * @return void
     * @throws \ezp\content\ValidationException If a validation problem has been found;
     */
    public function move( \ezp\content\Location $location, \ezp\content\Location $newParent )
    {
        // take care of :
        // 1. set parentId and path for $location
        // 2. changing path attribute to the subtree below $location
    }


    /**
     * Deletes the $locations and all descendants of $location. 
     * 
     * @param \ezp\content\Location $location
     * @return void
     * @throws \ezp\content\ValidationException If a validation problem has been found;
     */
    public function delete( \ezp\content\Location $location )
    {
        // take care of:
        // 1. removing the current location
        // 2. removing the content addressed by the location if there's no more
        // location
        // 3. do the same operations on the subtree (recursive calls through
        // children ?)
        // note: this is different from Content::delete()
    }


    /**
     * Assigns $section to the contents hold by $startingPoint location and
     * all contents hold by descendants location of $startingPoint
     * 
     * @param \ezp\content\Location $startingPoint
     * @param \ezp\content\Section $section
     * @return void
     * @throws \ezp\content\ValidationException If a validation problem has been found;
     */
    public function assignSection( \ezp\content\Location $startingPoint, \ezp\content\Section $section )
    {
    }
}
?>
