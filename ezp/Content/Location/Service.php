<?php
/**
 * File containing the ezp\Content\Location\Service class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location;
use ezp\Base\Exception,
    ezp\Base\Exception\Forbidden,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\Logic,
    ezp\Base\Service as BaseService,
    ezp\Base\Collection\Lazy,
    ezp\Base\Collection\LazyType,
    ezp\Content\Location,
    ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Content\Location\Proxy as ProxyLocation,
    ezp\Content\Location\Exception\NotFound as LocationNotFound,
    ezp\Content\Section,
    ezp\Content\Proxy as ProxyContent,
    ezp\Content\Query,
    ezp\Content\Query\Builder,
    ezp\Persistence\Content\Location as LocationValue,
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
     * @throws \ezp\Content\Location\Exception\NotFound
     * @todo Permission checking, is it possible w/o loading all content in the subtree?
     */
    public function copySubtree( Location $subtree, Location $targetLocation )
    {
        try
        {
            return $this->buildDomainObject(
                $this->handler->locationHandler()->copySubtree(
                    $subtree->id,
                    $targetLocation->id
                )
            );
        }
        catch ( NotFound $e )
        {
            throw new LocationNotFound( $e->identifier, $e );
        }
    }

    /**
     * Loads a location object from its $locationId
     * @param integer $locationId
     * @return \ezp\Content\Location
     * @throws \ezp\Content\Location\Exception\NotFound if no location is available with $locationId
     */
    public function load( $locationId )
    {
        try
        {
            $locationVO = $this->handler->locationHandler()->load( $locationId );
        }
        catch ( NotFound $e )
        {
            throw new LocationNotFound( $locationId, $e );
        }

        return $this->buildDomainObject( $locationVO );
    }

    /**
     * Load children of a location object sorted by sortField and sortOrder
     *
     * @access private Used internally by $location->children
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location[]
     * @todo Should take parentId as input to avoid cyclic references, the extra load will eventually be handled with
     *       identity map / object cache
     */
    public function children( Location $location )
    {
        // reuses contentService->find() for permissions and other reasons
        if ( $location->sortOrder === Location::SORT_ORDER_ASC )
            $order = Query::SORT_ASC;
        else
            $order = Query::SORT_DESC;

        $qb = new Builder();
        $qb->addCriteria( $qb->parentLocationId->eq( $location->id ) );

        switch ( $location->sortField )
        {
            case Location::SORT_FIELD_SECTION:
                $qb->addSortClause( $qb->sort->sectionName( $order ) );
                break;
            case Location::SORT_FIELD_PRIORITY:
                $qb->addSortClause( $qb->sort->locationPriority( $order ) );
                break;
            case Location::SORT_FIELD_PATH:
                $qb->addSortClause( $qb->sort->locationPathString( $order ) );
                break;
            case Location::SORT_FIELD_DEPTH:
                $qb->addSortClause( $qb->sort->locationDepth( $order ) );
                break;
            case Location::SORT_FIELD_MODIFIED:
                $qb->addSortClause( $qb->sort->dateModified( $order ) );
                break;
            case Location::SORT_FIELD_NAME:
                $qb->addSortClause( $qb->sort->contentName( $order ) );
                break;
            default:
                throw new Logic(
                    "\$location->sortField:'{$location->sortField}'",
                    "does mot currently have a corresponding SortClause"
                );
        }

        $children = array();
        $result = $this->repository->getContentService()->find( $qb->getQuery() );
        foreach ( $result as $childContent )
        {
            foreach ( $childContent->getLocations() as $child )
            {
                if ( $child->parentId == $location->id )
                {
                    $children[] = $child;
                    continue 2;
                }
            }
            throw new Logic(
                __METHOD__,
                "One of the returned content objects did not contain locations that where children of \$location"
            );
        }
        return $children;
    }

    /**
     * Creates the new $location in the content repository
     *
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location the newly created Location
     * @throws \ezp\Base\Exception\Logic If a validation problem has been found for $content
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided object
     */
    public function create( Location $location )
    {
        if ( $location->parentId == 0 )
        {
            throw new Logic( 'Location', 'Parent location is not defined' );
        }

        if ( !$this->repository->canUser( 'create', $location->getContent(), $location->getParent() ) )
            throw new Forbidden( 'Content', 'create' );

        // @todo Use fillStruct to not potentially pass empty properties to handler
        $struct = new CreateStruct();
        foreach ( $location->properties() as $name )
        {
            if ( property_exists( $struct, $name ) )
            {
                $struct->$name = $location->$name;
            }
        }

        $parent = $location->getParent();
        $struct->invisible = ( $parent->invisible == true ) || ( $parent->hidden == true );
        $struct->contentId = $location->contentId;
        $struct->priority = (int)$location->priority;

        $vo = $this->handler->locationHandler()->create( $struct );
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function update( Location $location )
    {
        if ( !$this->repository->canUser( 'edit', $location->getContent() ) )
            throw new Forbidden( 'Content', 'edit' );

        // @todo Use fillStruct to not potentially pass empty properties to handler
        $struct = new UpdateStruct;
        foreach ( $location->properties() as $name )
        {
            if ( property_exists( $struct, $name ) )
            {
                $struct->$name = $location->$name;
            }
        }

        if ( !$this->handler->locationHandler()->update( $struct, $location->id ) )
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided objects
     */
    public function swap( Location $location1, Location $location2 )
    {
        if ( !$this->repository->canUser( 'create', $location1->getContent(), $location2->getParent() ) )
            throw new Forbidden( 'Content', 'create' );
        if ( !$this->repository->canUser( 'create', $location2->getContent(), $location1->getParent() ) )
            throw new Forbidden( 'Content', 'create' );

        $location1Id = $location1->id;
        $location2Id = $location2->id;

        $this->handler->locationHandler()->swap( $location1Id, $location2Id );

        // @todo shouldn't content objects be swapped on location objects here?

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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to hide provided object
     */
    public function hide( Location $location )
    {
        if ( !$this->repository->canUser( 'hide', $location->getContent(), $location ) )
            throw new Forbidden( 'Content', 'hide' );

        $this->handler->locationHandler()->hide( $location->id );

        // Get VO & update hidden property
        $location->getState( 'properties' )->hidden = true;

        $children = $location->getChildren();
        if ( $children instanceof Lazy && !$children->isLoaded() )
            return $location;

        foreach ( $children as $child )
        {
            $child->getState( 'properties' )->invisible = true;
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to (un)hide provided object
     */
    public function unhide( Location $location )
    {
        if ( !$this->repository->canUser( 'hide', $location->getContent(), $location ) )
            throw new Forbidden( 'Content', 'unhide' );

        $this->handler->locationHandler()->unHide( $location->id );

        // Get VO & update hidden property
        $location->getState( 'properties' )->hidden = false;

        $children = $location->getChildren();
        if ( $children instanceof Lazy && !$children->isLoaded() )
            return $location;

        foreach ( $children as $child )
        {
            $child->getState( 'properties' )->invisible = false;
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
     * @todo Figure out a way to do permissions w/o loading whole tree
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
     * @throws \ezp\Base\Exception\NotFound if no location is available with $locationId
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to remove provided object
     * @todo Do we need to check permissions for delete on children? Or should we document that
     * giving access to deleting implicit gives a user access to remove all childes no matter what?
     */
    public function delete( Location $location )
    {
        if ( !$this->repository->canUser( 'remove', $location->getContent(), $location ) )
            throw new Forbidden( 'Content', 'remove' );

        $this->handler->locationHandler()->removeSubtree( $location->id );
        $this->refreshDomainObject( $location, $location->getState( 'properties' ) );
    }

    /**
     * Assigns $section to the contents held by $startingPoint location and
     * all contents held by descendants location of $startingPoint
     *
     * @param \ezp\Content\Location $startingPoint
     * @param \ezp\Content\Section $section
     * @return void
     * @todo Figure out how to do permission checks w/o loading whole tree
     */
    public function assignSection( Location $startingPoint, Section $section )
    {
        $this->handler->locationHandler()->setSectionForSubtree( $startingPoint->id, $section->id );
        $this->refreshDomainObject( $startingPoint );
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
        $location = new ConcreteLocation( new ProxyContent( $vo->contentId, $this->repository->getContentService() ) );

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
            'properties' => $vo,
            'parent' => new ProxyLocation( $vo->parentId, $this ),
            'children' => new LazyType(
                'ezp\\Content\\Location',
                $this,
                // api uses location to be able to use sort info
                $location,
                'children'
            )
        );
        // Check if associated content also needs to be refreshed
        if ( $vo->contentId != $location->contentId )
        {
            $newState['content'] = new ProxyContent( $vo->contentId, $this->repository->getContentService() );
        }
        $location->setState( $newState );

        return $location;
    }
}
