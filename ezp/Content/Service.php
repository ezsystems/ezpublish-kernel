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
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Content,
    ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Section\Proxy as ProxySection,
    ezp\Content\Location\Proxy as ProxyLocation,
    ezp\Content\Type\Proxy as ProxyType,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\User\Proxy as ProxyUser,
    ezp\Content\Version\LazyCollection as LazyVersionCollection,
    ezp\Content\Field\LazyCollection as LazyFieldCollection,
    ezp\Content\Field\StaticCollection as StaticFieldCollection,
    ezp\Content\Search\Result,
    ezp\Persistence\Content as ContentValue,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    ezp\Persistence\Content\Query\Criterion\ContentId,
    ezp\Persistence\Content\Query\Criterion\LogicalOr,
    ezp\Persistence\Content\Query\Criterion\LogicalAnd,
    ezp\Persistence\Content\Relation\CreateStruct as RelationCreateStruct,
    ezp\Persistence\Content\Version as VersionValue,
    ezp\Persistence\Content\RestrictedVersion as RestrictedVersionValue;

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
        if ( !$content->sectionId && $content->getMainLocation() )
        {
            $struct->sectionId = $content->getMainLocation()->getParent()->getContent()->sectionId;
        }

        $this->fillStruct( $struct, $content, array( 'parentLocations', 'fields' ) );
        foreach ( $content->getLocations() as $location )
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
        foreach ( $content->getFields() as $field )
        {
            $fieldStruct = $field->getState( 'properties' );
            $fieldStruct->value = $field->fieldDefinition->type->toFieldValue();
            $struct->fields[] = $fieldStruct;
        }
        $vo = $this->handler->contentHandler()->create( $struct );
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
        // go through all locations to create or update them?

        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $content, array( "versionNo", "userId", "fields" ) );
        $struct->userId = $content->ownerId;
        $struct->versionNo = $content->currentVersionNo;

        foreach ( $content->getFields() as $field )
        {
            $fieldStruct = $field->getState( 'properties' );
            $fieldStruct->value = $field->fieldDefinition->type->toFieldValue();
            $struct->fields[] = $fieldStruct;
        }

        return $this->buildDomainObject(
            $this->handler->contentHandler()->update( $struct  )
        );
    }

    /**
     * Loads a content from its id ($contentId)
     *
     * @param mixed $contentId
     * @param int $version
     * @return \ezp\Content
     * @throws \ezp\Base\Exception\NotFound if content could not be found
     */
    public function load( $contentId, $version = null )
    {
        if ( !$contentId )
            throw new NotFound( 'Content', $contentId );

        if ( $version === null )
            $contentVO = $this->handler->searchHandler()->findSingle( new ContentId( $contentId ) );
        else
            $contentVO = $this->handler->contentHandler()->load( $contentId, $version );

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
     * @access private For use in $content->versions, hence why it returns native array
     * @param int $contentId
     * @return \ezp\Content\Version[]
     * @todo Since this is internal it can just as well take Content as input to avoid extra load
     *
     * @internal
     */
    public function listVersions( $contentId )
    {
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
     * @access private For use in $version->getFields() when lazy loaded, hence why it returns native array
     * @param \ezp\Content\Version $version
     * @return \ezp\Content\Field[]
     * @throws \ezp\Base\Exception\NotFound If version can not be found
     * @todo Deal with translations
     */
    public function loadFields( Version $version )
    {
        if ( !$version->versionNo )
            throw new NotFound( 'Version', $version->versionNo );

        $content = $this->load( $version->contentId, $version->versionNo );
        $versionVo = $content->getState( 'properties' )->version;

        $version->setState( array( 'properties' => $versionVo ) );
        $defaultFields = new StaticFieldCollection( $version );
        $fields = array();
        foreach ( $defaultFields as $identifier => $field )
        {
            foreach ( $versionVo->fields as $voField )
            {
                if ( $field->fieldDefinitionId == $voField->fieldDefinitionId )
                {
                    // @todo Attach observer to Field. See why this method isn't used in buildVersionDomainObject
                    $fields[$identifier] = $field->setState( array( 'properties' => $voField ) );
                    $fields[$identifier]->setValue( $voField->value->data );

                    // Make the FieldType an Observer for content/publish
                    $content->attach( $fields[$identifier]->getFieldDefinition()->getType(), 'content/publish' );
                    continue 2;
                }

            }
            throw new Logic( 'field:' . $identifier, 'could not find this field in returned Version value data'  );
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
     *       So should likewise be marked as private and take Version is argument?
     * @param int $contentId
     * @param int|null $version
     */
    public function loadRelations( $contentId, $version = null )
    {
        //$this->handler->contentHandler()->loadRelations();
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
     * If $srcVersion (default value) is null, currentVersion from $content will be taken
     *
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $srcVersion The source version to use. currentVersion is used if not specificed
     * @return \ezp\Content\Version
     * @throws \ezp\Base\Exception\NotFound
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
     * Triggers a content search against $query.
     * $query should have been built using {@link \ezp\Content\Query\Builder} interface:
     * <code>
     * $qb = new ezp\Content\Query\Builder();
     * $qb->addCriteria(
     *     $qb->fullText->like( 'eZ Publish' ),
     *     $qb->urlAlias->like( '/cms/amazing/*' ),
     *     $qb->contentTypeId->eq( 'folder' ),
     *     $qb->field->eq( 'author', 'community@ez.no' )
     * )->addSortClause(
     *     $qb->sort->field( 'folder', 'name', Query::SORT_ASC ),
     *     $qb->sort->dateCreated( Query::SORT_DESC )
     * )->setOffset( 5 )->setLimit( 15 );
     * $contentList = $contentService->find( $qb->getQuery() );
     * </code>
     *
     * @param \ezp\Content\Query $query
     * @return \ezp\Content\Result
     * @todo User permissions
     * @todo Translation filter
     */
    public function find( Query $query )
    {
        // Permission handling
        $definition = ConcreteContent::definition();
        $limitationArray = $this->repository->getUser()->hasAccessTo( $definition['module'], 'read' );
        if ( $limitationArray === false )
        {
            // User does not have read access to content at all
            return new Result( array(), 0, $query );
        }
        else if ( $limitationArray !== true )
        {
            // Create OR conditions for every "policy" that contains AND conditions for limitations
            $orCriteria = array();
            foreach ( $limitationArray as $limitationSet )
            {
                $andCriteria = array();
                foreach ( $limitationSet as $limitationKey => $limitationValues )
                {
                    if ( !isset( $definition['functions']['read'][$limitationKey]['query'] ) )
                    {
                        throw new Logic(
                            "\$definition[functions][read][{$limitationKey}][query]",
                            "could not find limitation query function on ezp\\Content\\Concrete::definition()"
                        );
                    }

                    $limitationQueryFn = $definition['functions']['read'][$limitationKey]['query'];
                    if ( !is_callable( $limitationQueryFn ) )
                    {
                        throw new Logic(
                            "\$definition[functions][read][{$limitationKey}][query]",
                            "query function from ezp\\Content\\Concrete::definition() is not callable"
                        );
                    }

                    $andCriteria[] = $limitationQueryFn( $limitationValues, $this->repository );
                }
                $orCriteria[] = isset( $andCriteria[1] ) ? new LogicalAnd( $andCriteria ) : $andCriteria[0];
            }

            // Merge with $query->criterion
            if ( $query->criterion instanceof LogicalAnd )
            {
                $query->criterion->criteria[] = isset( $orCriteria[1] ) ? new LogicalOr( $orCriteria ) : $orCriteria[0];
            }
            else
            {
                $query->criterion = new LogicalAnd(
                    array(
                         $query->criterion,
                         isset( $orCriteria[1] ) ? new LogicalOr( $orCriteria ) : $orCriteria[0]
                    )
                );
            }
        }

        // @TODO: handle $translations with $query object (not implemented yet)
        $translations = null;
        $result = $this->handler->searchHandler()->find(
            $query->criterion,
            $query->offset,
            $query->limit,
            $query->sortClauses,
            $translations
        );

        $aContent = array();
        foreach ( $result->content as $contentVo )
        {
            $aContent[] = $this->buildDomainObject( $contentVo );
        }

        return new Result( $aContent, $result->count, $query );
    }

    /**
     * Triggers a content search against $query and returns only one content object
     *
     * @param \ezp\Content\Query $query
     * @return \ezp\Content
     * @todo User permissions
     * @todo Translation filter
     */
    public function findSingle( Query $query )
    {
        // @TODO: handle $translations with $query object (not implemented yet)
        $translations = null;
        $contentVo = $this->handler->searchHandler()->findSingle(
            $query->criterion,
            $translations
        );

        return $this->buildDomainObject( $contentVo );
    }

    /**
     * Deletes $version
     * @param \ezp\Content\Version
     */
    public function deleteVersion( Version $version )
    {
        $this->handler->contentHandler()->deleteVersion( $version->id );
    }

    /**
     * Publishes $version of $content
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $version
     *
     * @throws \ezp\Base\Exception\Logic if $version doesn't have the DRAFT status
     * @throws \ezp\Base\Exception\Logic if $version and $content don't match
     *
     * @return \ezp\Content The updated, published Content
     */
    public function publish( Content $content, Version $version )
    {
        // Only drafts can be published
        if ( $version->status !== Version::STATUS_DRAFT )
        {
            throw new Logic( '$version->status', 'Version should be in Version::STATUS_DRAFT state' );
        }

        // The version should belong to the given content
        if ( $version->contentId !== $content->id )
        {
            throw new Logic( '$version->contentId', 'Content/Version arguments mismatch' );
        }

        // $this->handler->beginTransaction();

        // Archive the previous version if it exists
        if ( $version->versionNo > 1 )
        {
            $this->handler->contentHandler()->setStatus( $content->id, Version::STATUS_ARCHIVED, $content->currentVersion->versionNo );
        }
        // @todo Maybe allow this to be part of Update struct to reduce backend calls
        $this->handler->contentHandler()->setStatus( $content->id, Version::STATUS_PUBLISHED, $version->versionNo );

        // @todo: Update $content->currentVersionNo to $version->versionNo

        $updateStruct = new UpdateStruct();
        $updateStruct->id = $content->id;
        $updateStruct->userId = $version->creatorId;
        $updateStruct->versionNo = $version->versionNo;
        // @todo Get names using the appropriate call
        // $struct->name = ...

        $contentVo = $this->handler->contentHandler()->publish( $updateStruct );



        // $this->handler->commit();

        $contentDO = $this->buildDomainObject( $contentVo );

        $this->notify( 'content/publish', array( $contentDO, $version ) );

        return $contentDO;
    }

    /**
     * Build a content Domain Object from a content Value object returned by Persistence
     * @param \ezp\Persistence\Content $vo
     * @return \ezp\Content
     */
    protected function buildDomainObject( ContentValue $vo )
    {
        // @todo Using ConcreteType for now, but I guess there is something wrong here
        //       Shouldn't a ProxyType be used, but then, which Content Type ID to use?
        //       Should I use the one provided in setState?
        $content = new Concrete( new ConcreteType, new ProxyUser( $vo->ownerId, $this->repository->getUserService() ) );
        // @todo Attach observer to Content
        $content->setState(
            array(
                "section" => new ProxySection( $vo->sectionId, $this->repository->getSectionService() ),
                "contentType" => new ProxyType( $vo->typeId, $this->repository->getContentTypeService() ),
                "versions" => new LazyVersionCollection( $this, $vo->id ),//@todo Avoid throwing away version info on $vo
                "properties" => $vo
            )
        );

        $locationService = $this->repository->getLocationService();
        $locations = $content->getLocations();
        foreach ( $vo->locations as $locationValue )
        {
            $locations[] = $location = new Location\Concrete( $content );
            $location->setState(
                array(
                    "properties" => $locationValue,
                    "parent" => new ProxyLocation( $locationValue->parentId, $locationService ),
                    "children" => new Lazy(
                        "ezp\\Content\\Location",
                        $locationService,
                        $location, // api uses location to be able to use sort info
                        'children'
                    )
                )
            );
        }

        $this->attach( $content, 'content/publish' );

        return $content;
    }

    /**
     * Builds a version Domain Object from its value object returned by persistence
     *
     * @param \ezp\Content $content
     * @param \ezp\Persistence\Content\Version|\ezp\Persistence\Content\RestrictedVersion $versionVo
     * @return \ezp\Content\Version
     */
    protected function buildVersionDomainObject( Content $content, $vo )
    {
        if ( !$vo instanceof VersionValue && !$vo instanceof RestrictedVersionValue )
            throw new InvalidArgumentType( '$versionVo', 'Version or RestrictedVersion', $vo );

        $version = new Version( $content );

        $version->setState( array( 'properties' => $vo ) );

        // lazy load fields if Version does not contain fields
        if ( $vo instanceof RestrictedVersionValue )
        {
            $version->setState( array( 'fields' => new LazyFieldCollection( $this, $version ) ) );
            return $version;
        }

        $version->setState( array( 'fields' => new StaticFieldCollection( $version ) ) );
        // @todo Deal with translations
        foreach ( $version->getFields() as $identifier => $field )
        {
            foreach ( $vo->fields as $voField )
            {
                if ( $field->fieldDefinitionId == $voField->fieldDefinitionId )
                {
                    $field->setState( array( 'properties' => $voField ) );
                    $field->setValue( $voField->value->data );

                    // Make the FieldType an observer of content/publish
                    $content->attach( $field->getFieldDefinition()->getType(), 'content/publish' );

                    continue 2;
                }

            }
            throw new Logic( 'field:' . $identifier, 'could not find this field in returned Version value data'  );
        }

        $this->attach( $version, 'content/publish' );

        return $version;
    }
}
