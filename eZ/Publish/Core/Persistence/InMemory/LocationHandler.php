<?php
/**
 * File containing the LocationHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location as LocationValue,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
 */
class LocationHandler implements LocationHandlerInterface
{
    const CHARS_ACCENT = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ';

    const CHARS_NOACCENT = 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn';

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function load( $locationId )
    {
        return $this->backend->load( 'Content\\Location', $locationId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function copySubtree( $sourceId, $destinationParentId )
    {
        $location = $this->load( $sourceId );
        $contentCopy = $this->handler->contentHandler()->copy( $location->contentId, false );

        $newLocation = $this->create(
            new CreateStruct(
                array(
                    "contentId" => $contentCopy->contentInfo->contentId,
                    "contentVersion" => $contentCopy->contentInfo->currentVersionNo,
                    "sortField" => $location->sortField,
                    "sortOrder" => $location->sortOrder,
                    "parentId" => $destinationParentId,
                )
            )
        );

        // Begin recursive call on children, if any
        foreach ( $this->backend->find( "Content\\Location", array( "parentId" => $sourceId ) ) as $child )
        {
            $this->copySubtree( $child->id, $newLocation->id );
        }

        return $newLocation;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function move( $sourceId, $destinationParentId )
    {
        $vo = $this->load( $sourceId );
        $newParentVO = $this->load( $destinationParentId );
        $oldPathString = $vo->pathString;
        $newPathString = $newParentVO->pathString . $sourceId . '/';
        $oldPathIdentificationString = $vo->pathIdentificationString;
        $newPathIdentificationString = '';
        if ( $newParentVO->parentId == 1 )
            $newPathIdentificationString = $this->getStrippedContentName( $vo );
        else
            $newPathIdentificationString = $this->getPathIdentificationString( $newParentVO ) . '/' . $this->getStrippedContentName( $vo );

        $this->backend->update(
            'Content\\Location',
            $sourceId,
            array(
                'parentId' => $destinationParentId,
                'pathString' => $newPathString,
                'pathIdentificationString' => $newPathIdentificationString
            )
        );

        $children = $this->backend->find( 'Content\\Location', array( 'pathString' => "$vo->pathString%" ) );
        foreach ( $children as $child )
        {
            $this->backend->update(
                'Content\\Location',
                $child->id,
                array(
                    'pathString' => str_replace( $oldPathString, $newPathString, $child->pathString ),
                    'pathIdentificationString' => str_replace( $oldPathIdentificationString, $newPathIdentificationString, $child->pathIdentificationString )
                )
            );
        }

        $this->updateSubtreeModificationTime( $newParentVO->pathString );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function markSubtreeModified( $locationId, $timeStamp = null )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function hide( $id )
    {
        $this->backend->update( 'Content\\Location', $id, array( 'hidden' => true, 'invisible' => true ) );

        $locationVO = $this->backend->load( 'Content\\Location', $id );
        $this->backend->updateByMatch(
            "Content\\Location",
            array( "pathString" => "{$locationVO->pathString}%" ),
            array( "invisible" => true )
        );

       $this->updateSubtreeModificationTime( $this->getParentPathString( $locationVO->pathString ) );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function unHide( $id )
    {
        $this->backend->update( 'Content\\Location', $id, array( 'hidden' => false, 'invisible' => false ) );

        $locationVO = $this->backend->load( 'Content\\Location', $id );
        $hiddenLocations = $this->backend->find(
            "Content\\Location",
            array(
                "pathString" => "{$locationVO->pathString}%",
                "hidden" => true
            )
        );

        $invisibleLocations = $this->backend->find(
            "Content\\Location",
            array(
                "pathString" => "{$locationVO->pathString}%",
                "invisible" => true,
                "hidden" => false
            )
        );

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
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
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
            )
        );
        $this->backend->update(
            'Content\\Location',
            $locationId2,
            array(
                'contentId' => $location1->contentId,
            )
        );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $location1->pathString ) );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $location2->pathString ) );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function update( UpdateStruct $location, $locationId )
    {
        return $this->backend->update(
            'Content\\Location',
            $locationId,
            (array)$location
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function create( CreateStruct $locationStruct )
    {
        $parentId = $locationStruct->parentId;
        $parent = $this->load( $parentId );
        $params = (array)$locationStruct;
        $params['parentId'] = $parentId;
        $params['depth'] = $parent->depth + 1;
        $params['hidden'] = (bool)$locationStruct->hidden;
        if ( !isset( $params['remoteId'] ) )
        {
            $params['remoteId'] = md5( uniqid( 'Content\\Location', true ) );
        }

        // Creation, then update for pathString/pathIdentificationString/mainLocationId
        $mainLocationId = null;
        $otherLocationsForContent = $this->backend->find( 'Content\\Location', array( 'contentId' => $locationStruct->contentId ) );
        if ( !empty( $otherLocationsForContent ) )
        {
            $mainLocationId = $otherLocationsForContent[0]->id;
        }
        $vo = $this->backend->create( 'Content\\Location', $params );
        $pathString = $parent->pathString . $vo->id . '/';
        $this->backend->update(
            'Content\\Location',
            $vo->id,
            array(
                'pathString' => $pathString,
                'pathIdentificationString' => $this->getPathIdentificationString( $vo ),
                'mainLocationId' => isset( $mainLocationId ) ? $mainLocationId : $vo->id
            )
        );
        $this->updateSubtreeModificationTime( $this->getParentPathString( $parent->pathString ) );
        return $this->load( $vo->id );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function removeSubtree( $locationId )
    {
        $location = $this->load( $locationId );

        // Begin recursive call on children, if any
        $directChildren = $this->backend->find( 'Content\\Location', array( 'parentId' => $locationId ) );
        if ( !empty( $directChildren ) )
        {
            foreach ( $directChildren as $child )
            {
                $this->removeSubtree( $child->id );
            }
        }

        $this->delete( $locationId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function setSectionForSubtree( $locationId, $sectionId )
    {
        $location = $this->load( $locationId );
        $aContentIds = array( $location->contentId );
        $children = $this->backend->find( 'Content\\Location', array( 'pathString' => "$location->pathString%" ) );
        foreach ( $children as $child )
        {
            // Only get main locations
            if ( $child->mainLocationId == $child->id )
            {
                $aContentIds[] = $child->contentId;
            }
        }

        $this->backend->updateByMatch(
            'Content\\ContentInfo',
            array( 'contentId' => $aContentIds ),
            array( 'sectionId' => $sectionId ),
            true,
            'contentId'
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function storeUrlAliasPath( $path, $locationId, $languageName = null, $alwaysAvailable = false )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function createCustomUrlAlias( $alias, $locationId, $forwarding = false, $languageName = null, $alwaysAvailable = false )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function createUrlHistoryEntry( $historicUrl, $locationId )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function listUrlsForLocation( $locationId, $urlType )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function removeUrlsForLocation( $locationId, array $urlIdentifier )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function getPath( $locationId, $language )
    {
    }

    /**
     * Removes a location from its $locationId (but not its descendants)
     * Content which looses its main Location will get the first
     * of its other Locations assigned as the new main Location.
     * If content has no location left, it's removed from backend
     *
     * @param mixed $locationId
     */
    public function delete( $locationId )
    {
        $location = $this->load( $locationId );
        $this->backend->delete( 'Content\\Location', $locationId );
        $remainingLocations = $this->backend->find( 'Content\\Location', array( 'contentId' => $location->contentId ) );
        // If no remaining location for associated content, remove the content as well
        // Else, update the mainLocationId if needed
        if ( empty( $remainingLocations ) )
        {
            try
            {
                $this->handler->contentHandler()->deleteContent( $location->contentId );
            }
            // Ignoring a NotFound exception since the content handler also takes care of removing itself and locations
            catch ( NotFound $e )
            {
            }
        }
        else
        {
            $this->backend->updateByMatch(
                'Content\\Location',
                array( 'contentId' => $location->contentId ),
                array( 'mainLocationId' => $remainingLocations[0]->id )
            );
        }

        $this->updateSubtreeModificationTime( $this->getParentPathString( $location->pathString ) );
    }

    /**
     * Returns locations given a parent $locationId.
     *
     * @todo Requires approbation
     * @param mixed $locationId
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function loadByParentId( $locationId )
    {
        $result = $this->backend->find( "Content\\Location", array( "parentId" => $locationId ) );

        // If no result is found this might be caused by an unexisting location ID.
        // We call load() to trigger a NotFound exception in such case for consistencies
        // across the API.
        if ( empty( $result ) )
        {
            $this->load( $locationId );
        }

        return $result;
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

    /**
     * Returns pathIdentificationString for provided location value object
     * @param eZ\Publish\SPI\Persistence\Content\Location $vo
     * @return string
     */
    private function getPathIdentificationString( LocationValue $vo )
    {
        $parent = $this->load( $vo->parentId );
        if ( $vo->parentId == 1 )
        {
            return '';
        }

        if ( empty( $parent->pathIdentificationString ) )
        {
            return $this->getStrippedContentName( $vo );
        }

        return $parent->pathIdentificationString . '/' . $this->getStrippedContentName( $vo );
    }

    /**
     * Returns stripped content name from location value
     * All downcase, special chars to underscores
     * e.g. my_content_name
     * @param LocationValue $vo
     * @return string
     */
    private function getStrippedContentName( LocationValue $vo )
    {
        $version = $this->backend->find(
            "Content\\VersionInfo",
            array(
                "contentId" => $vo->contentId,
                "versionNo" => $this->backend->load( 'Content\\ContentInfo', $vo->contentId, 'contentId' )->currentVersionNo
            )
        );
        return isset( $version[0]->names["eng-GB"] )
            ? preg_replace(
                '`[^a-z0-9_]`i',
                '_',
                strtolower(
                    trim(
                        strtr(
                            // @todo Remove hardcoding of eng-GB
                            $version[0]->names["eng-GB"],
                            self::CHARS_ACCENT,
                            self::CHARS_NOACCENT
                        )
                    )
                )
            )
            : null;
    }
}
