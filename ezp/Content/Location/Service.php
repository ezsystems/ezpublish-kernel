<?php
/**
 * File containing the ezp\Content\Location\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location;
use ezp\Base\Exception,
    ezp\Base\Service as BaseService,
    ezp\Content\Location,
    ezp\Content\Proxy,
    ezp\Content\Section,
    ezp\Content\ContainerProperty,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\ValueObject;

/**
 * Location service, used for complex subtree operations
 */
class Service extends BaseService
{

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * @param Location $subtree
     * @param Location $targetLocation
     *
     * @return Location The newly created subtree
     */
    public function copy( Location $subtree, Location $targetLocation )
    {
    }

    /**
     * Loads a location object from its $locationId
     * @param integer $locationId
     * @return Location
     * @throws Exception\NotFound if no location is available with $locationId
     */
    public function load( $locationId )
    {

    }

    public function children( Location $location )
    {

    }

    /**
     * Creates the new $location in the content repository
     *
     * @param Location $location
     * @return Location the newly created Location
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function create( Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    /**
     * Updates $location in the content repository
     *
     * @param Location $location
     * @return Location the updated Location
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function update( Location $location )
    {
        // repo/storage stuff
        return $location;
    }

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @param Location $location1
     * @param Location $location2
     * @return void
     * @throws Exception\Validation If a validation problem has been found
     */
    public function swap( Location $location1, Location $location2 )
    {

    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @param Location $location
     * @return void
     * @throws Exception\Validation If a validation problem has been found
     */
    public function hide( Location $location )
    {
        // take care of :
        // 1. hiding $location
        // 2. making the whole subtree invisible
    }

    /**
     * Unhides the $location and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @param Location $location
     * @return void
     * @throws Exception\Validation If a validation problem has been found;
     */
    public function unhide( Location $location )
    {
        // take care of :
        // 1. unhiding $location
        // 2. making the whole subtree visible (unless we found a hidden
        // location)
    }

    /**
     * Moves $location under $newParent and updates all descendants of
     * $location accordingly.
     *
     * @param Location $location
     * @param Location $newParent
     * @return void
     * @throws Exception\Validation If a validation problem has been found;
     */
    public function move( Location $location, Location $newParent )
    {
        // take care of :
        // 1. set parentId and path for $location
        // 2. changing path attribute to the subtree below $location
    }

    /**
     * Deletes the $locations and all descendants of $location.
     *
     * @param Location $location
     * @return void
     * @throws Exception\Validation If a validation problem has been found;
     */
    public function delete( Location $location )
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
     * @param Location $startingPoint
     * @param Section $section
     * @return void
     * @throws Exception\Validation If a validation problem has been found;
     */
    public function assignSection( Location $startingPoint, Section $section )
    {
    }

    protected function buildDomainObject( ValueObject $vo )
    {
        if ( !$vo instanceof LocationValue )
        {
            throw new InvalidArgumentType( 'Value object', 'ezp\\Persistence\\Content\\Location', $vo );
        }

        $location = new Location( new Proxy( $this->repository->getContentService(), $valueObject->contentId ) );
        $location->setState(
            array(
                'parent' => new Proxy( $this, $vo->parentId ),
                'properties' => $vo
            )
        );

        // Container property (default sorting)
        $containerProperty = new ContainerProperty;
        $location->containerProperties[] = $containerProperty->setState(
            array(
                'locationId' => $vo->id,
                'sortField' => $vo->sortField,
                'sortOrder' => $vo->sortOrder,
                'location' => new Proxy( $this, $vo->id )
            )
        );

        return $location;
    }
}
