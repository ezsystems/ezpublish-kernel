<?php
/**
 * File containing the LocationHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Persistence\Content\Location\Handler as LocationHandlerInterface,
    ezp\Persistence\Content\Location\CreateStruct;

/**
 * @see ezp\Persistence\Content\Location\Handler
 *
 * @version //autogentag//
 */
class LocationHandler implements LocationHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function load( $locationId )
    {
        return $this->backend->load( 'Content\\Location', $locationId );
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function copySubtree( $sourceId, $destinationParentId )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function move( $sourceId, $destinationParentId )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function markSubtreeModified( $locationId, $timeStamp = null )
    {
    }


    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function hide( $id )
    {
        $this->backend->update( 'Content\\Location' , $id, array( 'hidden' => true, 'invisible' => true ) );

        $locationVO = $this->backend->load( 'Content\\Location', $id );
        $this->backend->updateByMatch( 'Content\\Location',
                                       array( 'pathString' => "{$locationVO->pathString}%" ),
                                       array( 'invisible' => true ) );

       $this->updateSubtreeModificationTime( $this->getParentPathString( $locationVO->pathString ) );
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function unHide( $id )
    {
        $this->backend->update( 'Content\\Location', $id, array( 'hidden' => false, 'invisible' => false ) );

        $locationVO = $this->backend->load( 'Content\\Location', $id );
        $hiddenLocations = $this->backend->find( 'Content\\Location',
                                                 array(
                                                     'pathString' => "{$locationVO->pathString}%",
                                                     'hidden' => true
                                                 )
                                               );

        $invisibleLocations = $this->backend->find( 'Content\\Location',
                                                    array(
                                                        'pathString' => "{$locationVO->pathString}%",
                                                        'invisible' => true,
                                                        'hidden' => false
                                                    ));

        $locationsToUnhide = array();
        // Loop against all invisible locations and figure out
        // if they are under a hidden one.
        // If this is the case, the location won't be made visible
        foreach ( $invisibleLocations as $loc )
        {
            foreach ( $hiddenLocations as $hiddenLoc )
            {
                if ( strpos( $loc->pathString, $hiddenLoc->pathString ) === 0 )
                {
                    continue 2;
                }
            }
            $locationsToUnhide[] = $loc->id;
        }

        $this->backend->updateByMatch( 'Content\\Location', array( 'id' => $locationsToUnhide ), array( 'invisible' => false ) );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $locationVO->pathString ) );
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function swap( $locationId1, $locationId2 )
    {
        $location1 = $this->backend->load( 'Content\\Location', $locationId1 );
        $content1 = $this->backend->load( 'Content', $location1->contentId );

        $location2 = $this->backend->load( 'Content\\Location', $locationId2 );
        $content2 = $this->backend->load( 'Content', $location2->contentId );

        $this->backend->update(
            'Content\\Location',
            $locationId1,
            array(
                'contentId' => $location2->contentId,
//                'pathIdentificationString' => trim( str_replace(' ', '_', strtolower( $content2->name ) ) )
            )
        );
        $this->backend->update(
            'Content\\Location',
            $locationId2,
            array(
                'contentId' => $location1->contentId,
//                'pathIdentificationString' => trim( str_replace(' ', '_', strtolower( $content1->name ) ) )
            )
        );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $location1->pathString ) );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $location2->pathString ) );
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function updatePriority( $locationId, $priority )
    {
        return $this->backend->update( 'Content\\Location', $locationId, array( 'priority' => $priority ) );
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function createLocation( CreateStruct $locationStruct, $parentId )
    {
        $parent = $this->load( $parentId );
        $params = (array)$locationStruct;
        $params['parentId'] = $parentId;
        $params['depth'] = $parent->depth + 1;
        $params['hidden'] = (bool)$locationStruct->hidden;
        if ( !isset( $params['remoteId'] ) )
        {
            $params['remoteId'] = md5( uniqid( 'Content\\Location', true ) );
        }

        // pathIdentificationString
        // @todo: support for accentuated chars
        $contentName = $this->backend->load( 'Content', $locationStruct->contentId )->name;
        $params['pathIdentificationString'] = trim( str_replace(' ', '_', strtolower( $contentName ) ) );

        // Creation, then update for pathString
        $vo = $this->backend->create( 'Content\\Location', $params );
        $pathString = $parent->pathString . $vo->id . '/';
        $this->backend->update( 'Content\\Location', $vo->id, array( 'pathString' => $pathString ) );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $parent->pathString ) );
        return $this->load( $vo->id );
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function removeSubtree( $locationId )
    {

    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function trashSubtree( $locationId )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function untrashLocation( $locationId, $newParentId = null )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function setSectionForSubtree( $locationId, $sectionId )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function storeUrlAliasPath( $path, $locationId, $languageName = null, $alwaysAvailable = false )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function createCustomUrlAlias( $alias, $locationId, $forwarding = false, $languageName = null, $alwaysAvailable = false )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function createUrlHistoryEntry( $historicUrl, $locationId )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function listUrlsForLocation( $locationId, $urlType )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function removeUrlsForLocation( $locationId, array $urlIdentifier )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function getPath( $locationId, $language )
    {
    }

    /**
     * @see ezp\Persistence\Content\Location\Handler
     */
    public function delete( $locationId )
    {
        $return = $this->backend->delete( 'Content\\Location', $locationId );
        if ( !$return )
            return $return;
    }

    /**
     * Updates subtree modification time for all locations starting from $startPathString
     * @param string $startPathString
     */
    private function updateSubtreeModificationTime( $startPathString )
    {
        $this->backend->updateByMatch(
            'Content\\Location',
            array( 'pathString' => $startPathString . '%' ),
            array( 'modifiedSubLocation' => time() )
        );
    }

    /**
     * Returns parent path string for $pathString
     * @param string $pathString
     * @return string
     */
    private function getParentPathString( $pathString )
    {
        return substr( $pathString, 0, -2 );
    }
}
?>
