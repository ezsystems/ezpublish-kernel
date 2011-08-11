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
    ezp\Base\Exception\Logic,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\ValueObject,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Content\Location\UpdateStruct;

/**
 * Location service, used for complex subtree operations
 */
class Service extends BaseService
{

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * @param \ezp\Content\Location $subtree
     * @param \ezp\Content\Location $targetLocation
     *
     * @return \ezp\Content\Location The newly created subtree
     */
    public function copy( Location $subtree, Location $targetLocation )
    {
    }

    /**
     * Loads a location object from its $locationId
     * @param integer $locationId
     * @return \ezp\Content\Location
     * @throws \ezp\Base\Exception\NotFound if no location is available with $locationId
     */
    public function load( $locationId )
    {
        $locationVO = $this->handler->locationHandler()->load( $locationId );
        if ( !$locationVO instanceof LocationValue )
        {
            throw new NotFound( 'Location', $locationId );
        }

        return $this->buildDomainObject( $locationVO );
    }

    public function children( Location $location )
    {

    }

    /**
     * Creates the new $location in the content repository
     *
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location the newly created Location
     * @throws \ezp\Base\Exception\Logic If a validation problem has been found for $content
     */
    public function create( Location $location )
    {
        if ( $location->parentId == 0 )
        {
            throw new Logic( 'Location', 'Parent location is not defined' );
        }

        $struct = new CreateStruct();
        foreach ( $location->properties() as $name => $value )
        {
            if ( property_exists( $struct, $name ) )
            {
                $struct->$name = $location->$name;
            }
        }

        $struct->invisible = ( $location->parent->invisible == true ) || ( $location->parent->hidden == true );
        $struct->contentId = $location->contentId;

        $vo = $this->handler->locationHandler()->createLocation( $struct, $location->parentId );
        $location->setState( array( 'properties' => $vo ) );

        // repo/storage stuff
        return $location;
    }

    /**
     * Updates $location in the content repository
     *
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location the updated Location
     * @throws \ezp\Base\Exception\Logic If a validation problem has been found for $location
     */
    public function update( Location $location )
    {
        $struct = new UpdateStruct;
        foreach ( $location->properties() as $name => $value )
        {
            if ( property_exists( $struct, $name ) )
            {
                $struct->$name = $location->$name;
            }
        }

        if ( !$this->handler->locationHandler()->updateLocation( $struct, $location->id ) )
        {
            throw new Logic( "Location #{$location->id}", 'Could not be updated' );
        }

        return $location;
    }

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @param \ezp\Content\Location $location1
     * @param \ezp\Content\Location $location2
     * @return void
     * @throws \ezp\Base\Exception\Validation If a validation problem has been found
     */
    public function swap( Location $location1, Location $location2 )
    {
        $location1Id = $location1->id;
        $location2Id = $location2->id;

        $this->handler->locationHandler()->swap( $location1Id, $location2Id );

        // Update Domain objects references
        $this->refreshDomainObject( $location1 );
        $this->refreshDomainObject( $location2 );
    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location $location, with updated hidden value
     * @todo Make children visibility update more dynamic with some kind of LazyLoadedCollection
     */
    public function hide( Location $location )
    {
        $this->handler->locationHandler()->hide( $location->id );

        // Get VO, update hidden property and re-inject the reference it to $location
        $state = $location->getState();
        $state['properties']->hidden = true;
        $location->setState( array( 'properties' => $state['properties'] ) );

        foreach ( $location->children as $child )
        {
            $childState = $child->getState();
            $childState['properties']->invisible = true;
            // Following line is not needed but present for clarification
            // $childState['properties'] is actually a reference of $child::$properties
            $child->setState( array( 'properties' => $childState['properties'] ) );
        }

        return $location;
    }

    /**
     * Unhides the $location and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location $location, with updated hidden value
     * @todo Make children visibility update more dynamic with some kind of LazyLoadedCollection
     */
    public function unhide( Location $location )
    {
        $this->handler->locationHandler()->unHide( $location->id );

        // Get VO, update hidden property and re-inject the reference to $location
        $state = $location->getState();
        $state['properties']->hidden = false;
        $location->setState( array( 'properties' => $state['properties'] ) );

        foreach ( $location->children as $child )
        {
            $childState = $child->getState();
            $childState['properties']->invisible = false;
            // Following line is not needed but present for clarification
            // $childState['properties'] is actually a reference of $child::$properties
            $child->setState( array( 'properties' => $childState['properties'] ) );
        }

        return $location;
    }

    /**
     * Moves $location under $newParent and updates all descendants of
     * $location accordingly.
     *
     * @param \ezp\Content\Location $location
     * @param \ezp\Content\Location $newParent
     * @return void
     * @throws \ezp\Base\Exception\Validation If a validation problem has been found;
     */
    public function move( Location $location, Location $newParent )
    {
        $this->handler->locationHandler()->move( $location->id, $newParent->id );
        $this->refreshDomainObject( $location );
    }

    /**
     * Deletes the $locations and all descendants of $location.
     *
     * @param \ezp\Content\Location $location
     * @return void
     * @throws \ezp\Base\Exception\Validation If a validation problem has been found;
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
     * @param \ezp\Content\Location $startingPoint
     * @param Section $section
     * @return void
     * @throws \ezp\Base\Exception\Validation If a validation problem has been found;
     */
    public function assignSection( Location $startingPoint, Section $section )
    {
    }

    /**
     * Builds Location domain object from $vo ValueObject returned by Persistence API
     * @param \ezp\Persistence\Location $vo Location value object (extending \ezp\Persistence\ValueObject)
     *                                      returned by persistence
     * @return \ezp\Content\Location
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    protected function buildDomainObject( LocationValue $vo )
    {
        $location = new Location( new Proxy( $this->repository->getContentService(), $vo->contentId ) );

        return $this->refreshDomainObject( $location, $vo );
    }

    /**
     * Refreshes provided $location. Useful if backend data has changed
     *
     * @param \ezp\Content\Location $location Location to refresh
     * @param \ezp\Persistence\Location $vo Location value object. If provided, $location will be updated with $vo's data
     * @return \ezp\Content\Location
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    protected function refreshDomainObject( Location $location, LocationValue $vo = null )
    {
        if ( $vo === null )
        {
            $vo = $this->handler->locationHandler()->load( $location->id );
        }

        $newState = array(
            'parent' => new Proxy( $this, $vo->parentId ),
            'properties' => $vo
        );
        // Check if associated content also needs to be refreshed
        if ( $vo->contentId != $location->contentId )
            $newState['content'] = new Proxy( $this->repository->getContentService(), $vo->contentId );
        $location->setState( $newState );

        // Container property (default sorting)
        $containerProperty = new ContainerProperty;
        $location->containerProperties[] = $containerProperty->setState(
            array(
                'locationId' => $vo->id,
                'sortField' => $vo->sortField,
                'sortOrder' => $vo->sortOrder,
                'location' => $location
            )
        );

        return $location;
    }
}
