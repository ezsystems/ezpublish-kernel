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
     * Loads a location object from its $locationId
     * @param integer $locationId
     * @return \ezp\Content\Location
     * @throws \ezp\Content\LocationNotFoundException if no location is available with $locationId
     */
    public function load( $locationId )
    {

    }


    public function children( \ezp\Content\Location $location )
    {

    }

    /**
     * Creates the new $location in the content repository
     * 
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location the newly created Location
     * @throws \ezp\Content\ValidationException If a validation problem has been found for $content
     */
    public function create( \ezp\Content\Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    /**
     * Updates $location in the content repository
     * 
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location the updated Location
     * @throws \ezp\Content\ValidationException If a validation problem has been found for $content
     */
    public function update( \ezp\Content\Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    /**
     * Swaps the contents hold by the $location1 and $location2
     * 
     * @param \ezp\Content\Location $location1 
     * @param \ezp\Content\Location $location2 
     * @return void
     * @throws \ezp\Content\ValidationException If a validation problem has been found
     */
    public function swap( \ezp\Content\Location $location1, \ezp\Content\Location $location2 )
    {

    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     * 
     * @param \ezp\Content\Location $location 
     * @return void
     * @throws \ezp\Content\ValidationException If a validation problem has been found
     */
    public function hide( \ezp\Content\Location $location )
    {
        // take care of :
        // 1. hidding $location
        // 2. making the whole subtree invisible
    }

    /**
     * Unhides the $location and marks visible all descendants of $locations
     * until a hidden location is found. 
     * 
     * @param \ezp\Content\Location $location 
     * @return void
     * @throws \ezp\Content\ValidationException If a validation problem has been found;
     */
    public function unhide( \ezp\Content\Location $location )
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
     * @param \ezp\Content\Location $location 
     * @param \ezp\Content\Location $newParent 
     * @return void
     * @throws \ezp\Content\ValidationException If a validation problem has been found;
     */
    public function move( \ezp\Content\Location $location, \ezp\Content\Location $newParent )
    {
        // take care of :
        // 1. set parentId and path for $location
        // 2. changing path attribute to the subtree below $location
    }


    /**
     * Deletes the $locations and all descendants of $location. 
     * 
     * @param \ezp\Content\Location $location 
     * @return void
     * @throws \ezp\Content\ValidationException If a validation problem has been found;
     */
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


    /**
     * Assigns $section to the contents hold by $startingPoint location and
     * all contents hold by descendants location of $startingPoint
     * 
     * @param \ezp\Content\Location $startingPoint 
     * @param \ezp\Content\Section $section 
     * @return void
     * @throws \ezp\Content\ValidationException If a validation problem has been found;
     */
    public function assignSection( \ezp\Content\Location $startingPoint, \ezp\Content\Section $section )
    {
    }
}
?>
