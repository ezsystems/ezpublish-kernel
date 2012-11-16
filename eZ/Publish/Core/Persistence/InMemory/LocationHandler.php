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
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;

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
     * Loads all locations for $contentId, optionally limited to a sub tree
     * identified by $rootLocationId
     *
     * @param int $contentId
     * @param int $rootLocationId
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     * @todo Add support for $rootLocationId when not child of node 1
     */
    public function loadLocationsByContent( $contentId, $rootLocationId = null )
    {
        if ( $rootLocationId )
            return $this->backend->find(
                'Content\\Location',
                array( 'contentId' => $contentId, 'pathString' => "%/{$rootLocationId}/%" )
            );

        return $this->backend->find(
            'Content\\Location',
            array( 'contentId' => $contentId )
        );
    }

    /**
     * Loads the data for the location identified by $remoteId.
     *
     * @param string $remoteId
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadByRemoteId( $remoteId )
    {
        $locations = $this->backend->find(
            'Content\\Location',
            array( 'remoteId' => $remoteId )
        );

        if ( empty( $locations ) )
            throw new NotFound( 'Location by remote id', $remoteId );
        else if ( isset( $locations[1] ) )
            throw new \RuntimeException( "Several Locations found with same remote id: {$remoteId}" );

        return $locations[0];
    }

    /**
     * Get all subtree locations for the given location (including), sorted by path string
     *
     * @param $location
     * @param array $locations
     *
     * @return array
     */
    protected function getSubtreeLocations( $location, &$locations = array() )
    {
        if ( empty( $locations ) ) $locations[] = $location;

        $subLocations = $this->backend->find( "Content\\Location", array( "parentId" => $location->id ) );
        usort(
            $subLocations,
            function ( $subLocationA, $subLocationB )
            {
                return strnatcmp( $subLocationA->pathString, $subLocationB->pathString );
            }
        );
        foreach ( $subLocations as $subLocation )
        {
            $locations[] = $subLocation;
            $this->getSubtreeLocations( $subLocation, $locations );
        }

        return $locations;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function copySubtree( $sourceId, $destinationParentId )
    {
        $sourceLocation = $this->load( $sourceId );
        $children = $this->getSubtreeLocations( $sourceLocation );
        $parentLocation = $this->load( $destinationParentId );
        $contentMap = array();
        $locationMap = array(
            $children[0]->parentId => array(
                "id" => $destinationParentId,
                "hidden" => $parentLocation->hidden,
                "invisible" => $parentLocation->invisible
            )
        );

        $locations = array();
        foreach ( $children as $child )
        {
            $locations[$child->contentId][] = $child->id;
        }

        $time = time();
        $mainLocations = array();
        $mainLocationsUpdate = array();
        foreach ( $children as $index => $child )
        {
            $originalContentInfo = $this->handler->contentHandler()->loadContentInfo( $child->contentId );

            if ( !isset( $contentMap[$child->contentId] ) )
            {
                $content = $this->handler->contentHandler()->copy(
                    $child->contentId,
                    $originalContentInfo->currentVersionNo
                );
                $newContentId = $content->versionInfo->contentInfo->id;

                $content = $this->handler->contentHandler()->publish(
                    $newContentId,
                    $content->versionInfo->versionNo,
                    new MetadataUpdateStruct(
                        array(
                            "publicationDate" => $time,
                            "modificationDate" => $time
                        )
                    )
                );

                $contentMap[$child->contentId] = $newContentId;
            }

            $createStruct = new CreateStruct();
            $createStruct->contentVersion = $originalContentInfo->currentVersionNo;
            $createStruct->hidden = $child->hidden;
            $createStruct->pathIdentificationString = $child->pathIdentificationString;
            $createStruct->priority = $child->priority;
            $createStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
            $createStruct->sortField = $child->sortField;
            $createStruct->sortOrder = $child->sortOrder;
            $createStruct->contentId = $contentMap[$child->contentId];
            $createStruct->parentId = $locationMap[$child->parentId]["id"];
            $createStruct->invisible = $createStruct->hidden ||
                $locationMap[$child->parentId]["hidden"] ||
                $locationMap[$child->parentId]["invisible"];

            // Use content main node if already set, otherwise use this node as main
            if ( isset( $mainLocations[$child->contentId] ) )
            {
                $createStruct->mainLocationId = $locationMap[$mainLocations[$child->contentId]]["id"];
            }
            else
            {
                $createStruct->mainLocationId = true;
                $mainLocations[$child->contentId] = $child->id;

                // If needed mark for later update
                if (
                    in_array( $child->mainLocationId, $locations[$child->contentId] ) &&
                    count( $locations[$child->contentId] ) > 1 &&
                    $child->id !== $child->mainLocationId
                )
                {
                    $mainLocationsUpdate[$child->contentId] = $child->mainLocationId;
                }
            }

            $newLocation = $this->create( $createStruct );

            $locationMap[$child->id] = array(
                "id" => $newLocation->id,
                "hidden" => $newLocation->hidden,
                "invisible" => $newLocation->invisible
            );
            if ( $index === 0 ) $copiedSubtreeRootLocation = $newLocation;
        }

        // Update main locations
        foreach ( $mainLocationsUpdate as $contentId => $mainLocationId )
        {
            $this->changeMainLocation(
                $contentMap[$contentId],
                $locationMap[$mainLocationId]["id"]
            );
        }

        // If subtree root is main location for its content, update subtree section to the one of the
        // parent location content
        if ( $copiedSubtreeRootLocation->mainLocationId === $copiedSubtreeRootLocation->id )
        {
            $this->setSectionForSubtree(
                $copiedSubtreeRootLocation->id,
                $this->handler->contentHandler()->loadContentInfo( $this->load( $destinationParentId )->contentId )->sectionId
            );
        }

        return $copiedSubtreeRootLocation;
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
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function markSubtreeModified( $locationId, $timestamp = null )
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
            array( 'id' => $aContentIds ),
            array( 'sectionId' => $sectionId ),
            true
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function storeUrlAliasPath( $path, $locationId, $languageCode = null, $alwaysAvailable = false )
    {
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function createCustomUrlAlias( $alias, $locationId, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
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
     * Changes main location of content identified by given $contentId to location identified by given $locationId
     *
     *
     * @param mixed $contentId
     * @param mixed $locationId
     *
     * @return void
     */
    public function changeMainLocation( $contentId, $locationId )
    {
        $this->backend->updateByMatch(
            "Content\\Location",
            array( "contentId" => $contentId ),
            array( "mainLocationId" => $locationId )
        );

        $parentLocation = $this->backend->load(
            "Content\\Location",
            $this->backend->load( "Content\\Location", $locationId )->parentId
        );
        $parentContent = $this->backend->load(
            "Content\\ContentInfo",
            $parentLocation->contentId
        );

        $this->setSectionForSubtree( $locationId, $parentContent->sectionId );
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
     * @param \eZ\Publish\SPI\Persistence\Content\Location $vo
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
                "versionNo" => $this->backend->load( 'Content\\ContentInfo', $vo->contentId )->currentVersionNo
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
