<?php
/**
 * File containing the ezp\Content\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Service as BaseService,
    ezp\Base\Collection\Lazy,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Proxy,
    ezp\Content,
    ezp\Content\Location,
    ezp\Content\Version,
    ezp\Content\Version\Collection as VersionCollection,
    ezp\Content\Field,
    ezp\Content\Field\Collection as FieldCollection,
    ezp\Content\Query,
    ezp\Content\Query\Builder,
    ezp\Persistence\ValueObject,
    ezp\Persistence\Content as ContentValue,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Criterion\Operator,
    ezp\Persistence\Content\Relation\CreateStruct as RelationCreateStruct,
    ezp\Persistence\Content\Version as VersionValue;

/**
 * Content service, used for Content operations
 *
 */
class Service extends BaseService
{
    /**
     * Creates the new $content in the content repository
     *
     * @param \ezp\Content $content
     * @return \ezp\Content The newly created content
     * @throws \ezp\Base\Exception\InvalidArgumentType If $content already has an id
     * @todo If/when we have some sort of object storage, use that to check if object is persisted instead of just id
     */
    public function create( Content $content )
    {
        if ( $content->id )
            throw new InvalidArgumentType( '$content->id', 'false' );

        $struct = new CreateStruct();
        $this->fillStruct( $struct, $content, array( 'parentLocations', 'fields' ) );
        foreach ( $content->locations as $location )
        {
            // @todo: Generate pathIdentificationString?
            // @todo set sort order and fields based on settings in type
            $struct->parentLocations[] = $this->fillStruct(
                new LocationCreateStruct(
                    array(
                        "pathIdentificationString" => "",
                        "sortField" => Location::SORT_FIELD_PUBLISHED,
                        "sortOrder" => Location::SORT_ORDER_DESC,
                    )
                ),
                $location,
                array( "contentId", "contentVersion", "mainLocationId" )
            );
        }
        foreach ( $content->fields as $field )
        {
            $struct->fields[] = $field->getState( 'properties' );
        }
        $vo = $this->handler->contentHandler()->create( $struct  );
        return $this->buildDomainObject( $vo );
    }

    /**
     * Updates $content in the content repository
     *
     * @param \ezp\Content $content
     * @return \ezp\Content
     * @throws Exception\Validation If a validation problem has been found for $content
     */
    public function update( Content $content )
    {
        // @todo : Do any necessary actions to update $content in the content repository
        // go through all locations to create or update them

        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $content, array( "versionNo", "userId", "fields" ) );
        $struct->userId = $content->ownerId;
        $struct->versionNo = $content->currentVersionNo;

        foreach ( $content->fields as $fields )
        {
            $struct->fields[] = $fields->getState( "properties" );
        }

        return $this->buildDomainObject(
            $this->handler->contentHandler()->update( $struct  )
        );
    }

    /**
     * Loads a content from its id ($contentId)
     *
     * @param int $contentId
     * @return \ezp\Content
     * @throws \ezp\Base\Exception\NotFound if content could not be found
     */
    public function load( $contentId )
    {
        $contentVO = $this->handler->searchHandler()->findSingle( new ContentId( $contentId ) );
        if ( !$contentVO instanceof ContentValue )
            throw new NotFound( 'Content', $contentId );

        return $this->buildDomainObject( $contentVO );
    }

    /**
     * Deletes a content from the repository
     *
     * @param \ezp\Content $content
     * @throws \ezp\Base\Exception\NotFound if content could not be found
     */
    public function delete( Content $content )
    {
        $this->handler->contentHandler()->delete( $content->id );
    }

    /**
     * List versions of a $content
     *
     * @param int $contentId
     * @return \ezp\Content\Version[]
     */
    public function listVersions( $contentId )
    {
        // @FIXME: should the return be an array or some sort of collection?
        $list = array();
        $contentHandler = $this->handler->contentHandler();
        $content = $this->load( $contentId );

        foreach ( $contentHandler->listVersions( $contentId ) as $versionVO )
        {
            $version = $this->buildVersionDomainObject( $content, $versionVO );
            $list[$versionVO->versionNo] = $version;
        }

        return $list;
    }

    /**
     * List fields for a content $version.
     *
     * @param \ezp\Content\Version $version
     * @return \ezp\Content\Field[]
     * @todo Language support
     */
    public function loadFields( Version $version )
    {
        $fields = array();
        $fieldsDef = array();
        $fieldsVo = $this->handler->contentHandler()->loadFields( $version->contentId, $version->versionNo );

        // First index fields definitions by id
        foreach ( $version->content->contentType->fields as $fieldDefinition )
        {
            $fieldsDef[$fieldDefinition->id] = $fieldDefinition;
        }

        foreach ( $fieldsVo as $fieldVo )
        {
            if ( isset( $fieldsDef[$fieldVo->fieldDefinitionId] ) )
            {
                $identifier = $fieldsDef[$fieldVo->fieldDefinitionId]->identifier;
                $fieldDo = new Field( $version, $fieldsDef[$fieldVo->fieldDefinitionId] );
                $fieldDo->setState( array( 'properties' => $fieldVo ) );
                $fields[$identifier] = $fieldDo;
            }
            // @todo: throw exception if not set ?
        }

        return $fields;
    }

    /**
     * Adds a $relation to $content
     *
     * @param \ezp\Content\Relation $relation
     * @param \ezp\Content $content
     * @param int|null $version
     */
    public function addRelation( Relation $relation, Content $content, $version = null )
    {
        $struct = $this->fillStruct( new RelationCreateStruct( array(
                                                                   'sourceContentId' => $content->id,
                                                                   'sourceContentVersion' => $version
                                                               ) ),
                                                               $relation,
                                                               array(
                                                                   'sourceContentVersion',
                                                                   'sourceFieldDefinitionId'
                                                               ) );
        return $relation->setState(
            array(
                "properties" => $this->handler->contentHandler()->addRelation( $struct )
                )
        );
    }

    /**
     * Loads content relations from its id ($contentId)
     *
     * @todo Implement it (should be similar to listVersions())
     *       and use it for $content->relations lazy collection
     * @param int $contentId
     * @param int|null $version
     */
    public function loadRelations( $contentId, $version = null )
    {
        //$this->handler->contentHandler()->loadRelations();
    }

    /**
     * Sends $content to trash
     *
     * @param \ezp\Content $content
     */
    public function trash( Content $content )
    {

    }

    /**
     * Restores $content from trash
     *
     * @param \ezp\Content $content
     */
    public function unTrash( Content $content )
    {

    }

    /**
     * Copies $content in $version and returns the new copy.
     * If no version is provided, all versions will be copied
     *
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $version
     * @return \ezp\Content
     */
    public function copy( Content $content, Version $version = null )
    {
        $versionNo = isset( $version ) ? $version->versionNo : false;
        return $this->buildDomainObject( $this->handler->contentHandler()->copy( $content->id , $versionNo ) );
    }

    /**
     * Creates a new draft version from $content in $srcVersion.
     *
     * Copies all fields from $content in $srcVersion and creates a new
     * version of the referred Content from it.
     * If $srcVersion (default value) is null, last version from $content will be taken
     *
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $srcVersion
     * @return \ezp\Persistence\Content\Version
     * @todo Language support
     * @todo User support
     */
    public function createDraftFromVersion( Content $content, Version $srcVersion = null )
    {
        if ( !isset( $srcVersion ) )
            $srcVersion = $content->currentVersion;

        $newVersionVo = $this->handler->contentHandler()->createDraftFromVersion( $content->id, $srcVersion->versionNo );
        $version = $this->buildVersionDomainObject( $content, $newVersionVo );
        $content->versions[$newVersionVo->versionNo] = $version;
        return $version;
    }

    /**
     * Build a content Domain Object from a content Value object returned by Persistence
     * @param \ezp\Persistence\Content $vo
     * @return \ezp\Content
     */
    protected function buildDomainObject( ContentValue $vo )
    {
        $content = new Content( new Type );
        $content->setState(
            array(
                "section" => new Proxy( $this->repository->getSectionService(), $vo->sectionId ),
                "contentType" => new Proxy( $this->repository->getContentTypeService(), $vo->typeId ),
                "versions" => new VersionCollection( $this, $vo->id ),
                "properties" => $vo
            )
        );

        $locationService = $this->repository->getLocationService();
        foreach ( $vo->locations as $locationValue )
        {
            $content->locations[] = $location = new Location( $content );
            $location->setState(
                array(
                    "properties" => $locationValue,
                    "parent" => new Proxy( $locationService, $locationValue->parentId ),
                    "children" => new Lazy(
                        "ezp\\Content\\Location",
                        $locationService,
                        $location, // api seems to use location to be able to get out sort info as well
                        "children" // Not implemented yet so this collection will return empty array atm
                    )
                )
            );
        }

        return $content;
    }

    /**
     * Builds a version Domain Object from its value object returned by persistence
     *
     * @param \ezp\Content $content
     * @param \ezp\Persistence\Content\Version $versionVo
     * @return \ezp\Content\Version
     */
    protected function buildVersionDomainObject( Content $content, VersionValue $versionVo )
    {
        $version = new Version( $content );
        $version->setState(
            array(
                'properties' => $versionVo,
                'fields' => new FieldCollection( $this, $version )
            )
        );

        return $version;
    }
}
