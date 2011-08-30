<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Content\Handler as ContentHandlerInterface,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Criterion\Operator,
    ezp\Content\Version,
    ezp\Base\Exception\NotFound,
    RuntimeException;

/**
 * @see ezp\Persistence\Content\Handler
 */
class ContentHandler implements ContentHandlerInterface
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
     * @see ezp\Persistence\Content\Handler
     */
    public function create( CreateStruct $content )
    {
        $contentObj = $this->backend->create(
            'Content', array(
                'name' => $content->name,
                'typeId' => $content->typeId,
                'sectionId' => $content->sectionId,
                'ownerId' => $content->ownerId,
                'currentVersionNo' => 1,
            )
        );
        $version = $this->backend->create(
            'Content\\Version',
            array(
                'modified' => time(),
                'creatorId' => $content->ownerId,
                'created' => time(),
                'contentId' => $contentObj->id,
                'state' => Version::STATUS_DRAFT,
            )
        );
        foreach ( $content->fields as $field )
        {
            $version->fields[] = $this->backend->create(
                'Content\\Field',
                array( 'versionNo' => $version->id ) + (array)$field // @todo versionNo should be version number
            );
        }
        $contentObj->version = $version;

        $locationHandler = $this->handler->locationHandler();
        foreach ( $content->parentLocations as $locationStruct )
        {
            $locationStruct->contentId = $contentObj->id;
            $locationStruct->contentVersion = $contentObj->currentVersionNo;
            $contentObj->locations[] = $locationHandler->create( $locationStruct );
        }
        return $contentObj;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function createDraftFromVersion( $contentId, $srcVersion = false )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @param int $contentId
     * @param int|false $version Copy all versions if left false
     * @return \ezp\Persistence\Content
     * @throws \ezp\Base\Exception\NotFound If content or version is not found
     */
    public function copy( $contentId, $version )
    {
        $content = $this->backend->load( "Content", $contentId );
        if ( !$content )
            throw new NotFound( "Content", "contentId: $contentId" );

        $contentObj = $this->backend->create(
            "Content", array(
                "name" => $content->name,
                "typeId" => $content->typeId,
                "sectionId" => $content->sectionId,
                "ownerId" => $content->ownerId,
                "currentVersionNo" => 1, // @todo use correct number
            )
        );

        foreach (
            $this->backend->find(
                "Content\\Version",
                $version === false ?
                array( "contentId" => $content->id ) :
                array( "contentId" => $content->id, "versionNo" => $version )
            ) as $version )
        {
            $this->backend->create(
                "Content\\Version",
                array(
                    "versionNo" => $version->versionNo,
                    "modified" => time(),
                    "creatorId" => $version->creatorId,
                    "created" => time(),
                    "contentId" => $contentObj->id,
                    "state" => $version->state,
                )
            );
        }

        // @todo Copy fields!

        return $contentObj;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function load( $id, $version, $translations = null )
    {
        $content = $this->backend->load( 'Content', $id );
        if ( !$content )
            return null;

        $versions = $this->backend->find( 'Content\\Version', array( 'contentId' => $content->id, "versionNo" => $version ) );
        $versions[0]->fields = $this->backend->find( 'Content\\Field', array( 'versionNo' => $versions[0]->id ) );

        $content->version = $version[0];
        // @todo Loading locations by content object id should be possible using handler API.
        $content->locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $content->id  ) );
        return $content;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function setState( $contentId, $state, $version )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function setObjectState( $contentId, $stateGroup, $state )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function getObjectState( $contentId, $stateGroup )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function update( UpdateStruct $content )
    {
        // @todo Assume the attached version to Content is the one that should be updated
        $this->backend->update(
            "Content",
            $content->id,
            array(
                "ownerId" => $content->userId,
                "currentVersionNo" => $content->versionNo,
                "name" => $content->name,
            )
        );

        // @todo update fields

        return $this->backend->load( "Content", $content->id );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function delete( $contentId )
    {
        $this->backend->delete( "Content", $contentId );

        $versions = $this->backend->find( 'Content\\Version', array( 'contentId' => $contentId ) );
        foreach ( $versions as $version )
        {
            $fields = $this->backend->find( 'Content\\Field', array( 'versionNo' => $version->id ) );
            foreach ( $fields as $field )
                $this->backend->delete( 'Content\\Field', $field->id );

            $this->backend->delete( 'Content\\Version', $version->id );
        }

        // @todo Deleting Locations by content object id should be possible using handler API?
        $locationHandler = $this->handler->locationHandler();
        $locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $contentId ) );
        foreach ( $locations as $location )
        {
            $locationHandler->removeSubtree( $location->id );
        }
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function trash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function untrash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     * @throws \ezp\Base\Exception\NotFound If no version found
     */
    public function listVersions( $contentId )
    {
        $versions = $this->backend->find( "Content\\Version", array( "contentId" => $contentId ) );

        if ( empty( $versions ) )
            throw new NotFound( "Content\\Version", "contentId: $contentId" );

        return $versions;
    }
}
?>
