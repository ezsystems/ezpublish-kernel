<?php
/**
 * File containing the Content Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Mapper;

use ezp\Persistence\Content\Handler as BaseContentHandler,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Criterion;

/**
 * The Content Handler stores Content and ContentType objects.
 *
 * @version //autogentag//
 */
class Handler implements BaseContentHandler
{
    /**
     * Content gateway.
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Mapper.
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Registry for storages
     *
     * @var StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Creates a new content handler.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contentGateway
     */
    public function __construct(
        Gateway $contentGateway,
        Mapper $mapper,
        StorageRegistry $storageRegistry
    )
    {
        $this->contentGateway  = $contentGateway;
        $this->mapper          = $mapper;
        $this->storageRegistry = $storageRegistry;
    }

    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param \ezp\Persistence\Content\CreateStruct $struct Content creation struct.
     * @return \ezp\Persistence\Content Content value object
     * @todo Take care of initial locations!
     * @todo Method too complex, refactor!
     */
    public function create( CreateStruct $struct )
    {
        $content = $this->mapper->createContentFromCreateStruct(
            $struct
        );
        $content->id = $this->contentGateway->insertContentObject( $content );

        $version = $this->mapper->createVersionForContent( $content, 1 );

        $version->id = $this->contentGateway->insertVersion(
            $version
        );

        // TODO: Maybe it makes sense to introduce a dedicated
        // ContentFieldHandler for the legacy storage? Should be checked later, 
        // if this is possible and sensible
        foreach ( $struct->fields as $field )
        {
            $field->versionNo = $version->versionNo;
            $field->id = $this->contentGateway->insertNewField(
                $content,
                $field,
                $this->mapper->convertToStorageValue( $field )
            );
            $this->storageRegistry->getStorage( $field->type )
                ->storeFieldData( $field->id, $field->value );
            $version->fields[] = $field;
        }

        $content->versionInfos = array( $version );

        return $content;
    }

    /**
     * Creates a new draft version from $contentId in $version.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * @param int $contentId
     * @param int|bool $srcVersion
     * @return \ezp\Persistence\Content\Content
     */
    public function createDraftFromVersion( $contentId, $srcVersion )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Returns the raw data of a content object identified by $id, in a struct.
     *
     * @param int $id
     * @return \ezp\Persistence\Content Content value object
     */
    public function load( $id )
    {
        throw new Exception( "Not implemented yet." );
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
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Returns a single Content object found.
     *
     * Performs a {@link find()} query to find a single object. You need to
     * ensure, that your $criterion ensure that only a single object can be
     * retrieved.
     *
     * @param Criterion $criterion
     * @return \ezp\Persistence\Content
     */
    public function findSingle( Criterion $criterion )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $state can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED.
     *
     * @param int $contentId
     * @param int $state
     * @param int $version
     * @see ezp\Content
     * @return boolean
     */
    public function setState( $contentId, $state, $version )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Sets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @param mixed $state
     * @return boolean
     * @see ezp\Content
     */
    public function setObjectState( $contentId, $stateGroup, $state )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Gets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @return mixed
     * @see ezp\Content
     */
    public function getObjectState( $contentId, $stateGroup )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Updates a content object entity with data and identifier $content
     *
     * @param \ezp\Persistence\Content\UpdateStruct $content
     * @return \ezp\Persistence\Content
     */
    public function update( UpdateStruct $content )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * Removes the relations, but not the related objects. Alle subtrees of the
     * assigned nodes of this content objects are removed (recursivley).
     *
     * @param int $contentId
     * @return boolean
     */
    public function delete( $contentId )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Return the versions for $contentId
     *
     * @param int $contentId
     * @return array(Version)
     */
    public function listVersions( $contentId )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Fetch a content value object containing the values of the translation for $languageCode.
     *
     * This method might use field filters, if they are designed and available
     * at a later point in time.
     *
     * @param int $contentId
     * @param string $languageCode
     * @return \ezp\Persistence\Content\Content
     */
    public function fetchTranslation( $contentId, $languageCode )
    {
        throw new Exception( "Not implemented yet." );
    }
}
?>
