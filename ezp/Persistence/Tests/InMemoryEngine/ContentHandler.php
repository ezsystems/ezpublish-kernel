<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Persistence\Content\Handler as ContentHandlerInterface,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Criterion\Operator,
    ezp\Content\Version,
    RuntimeException;

/**
 * @see ezp\Persistence\Content\Handler
 *
 * @version //autogentag//
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
                array( 'versionNo' => $version->id ) + (array)$field
            );
        }
        $contentObj->version = $version;

        $locationHandler = $this->handler->locationHandler();
        foreach ( $content->parentLocations as $parentLocationId )
        {
            $contentObj->locations[] = $locationHandler->createLocation( $contentObj->id, $parentLocationId );
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
     * @see ezp\Persistence\Content\Handler
     */
    public function load( $id, $version, $translations = null )
    {
        $content = $this->backend->load( 'Content', $id );
        if ( !$content )
            return null;

        $versions = $this->backend->find( 'Content\\Version', array( 'contentId' => $content->id ) );
        foreach ( $versions as $version )
        {
            $version->fields = $this->backend->find( 'Content\\Field', array( 'versionNo' => $version->id ) );
        }
        $content->versionInfos = $versions;
        // @todo Loading locations by content object id should be possible using handler API.
        $content->locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $content->id  ) );
        return $content;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param $sort
     * @return array(ezp\Persistence\Content) Content value object.
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, $sort = null )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function findSingle( Criterion $criterion )
    {
        // Using "Coding by exception" anti pattern since it has been decided
        // not to implement search functionalities in InMemoryEngine
        if ( $criterion instanceof ContentId && $criterion->operator === Operator::EQ )
        {
            $content = $this->backend->load( "Content", $criterion->value[0] );

            $versions = $this->backend->find( "Content\\Version", array( "contentId" => $content->id ) );
            foreach ( $versions as $version )
            {
                $version->fields = $this->backend->find( "Content\\Field", array( "versionNo" => $version->id ) );
            }
            $content->versionInfos = $versions;
            // @todo Loading locations by content object id should be possible using handler API.
            $content->locations = $this->backend->find( "Content\\Location", array( "contentId" => $content->id  ) );
            return $content;
        }
        throw new RuntimeException( "@TODO: Implement" );
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
        // @todo Will need version number to be able to know which version to update.
        throw new RuntimeException( '@TODO: Implement' );
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

        // @todo Deleting Locations by content object id should be possible using handler API.
        $locationHandler = $this->handler->locationHandler();
        $locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $contentId ) );
        foreach ( $locations as $location )
        {
            $locationHandler->delete( $location->id );
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
     */
    public function listVersions( $contentId )
    {
        return $this->backend->find( 'Content\\Version', array( 'contentId' => $contentId ) );
    }
}
?>
