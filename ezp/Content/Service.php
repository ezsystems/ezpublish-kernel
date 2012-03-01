<?php
/**
 * File containing the ezp\Content\Service class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Service as BaseService,
    ezp\Base\Collection\LazyType,
    ezp\Base\Exception\Forbidden,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Configuration,
    ezp\Content,
    ezp\Content\Concrete as ConcreteContent,
    ezp\content\Proxy as ProxyContent,
    ezp\Content\Section\Proxy as ProxySection,
    ezp\Content\Location\Proxy as ProxyLocation,
    ezp\Content\Type\Proxy as ProxyType,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Version\Proxy as ProxyVersion,
    ezp\Content\Version\Concrete as ConcreteVersion,
    ezp\User\Proxy as ProxyUser,
    ezp\Content\Version\LazyCollection as LazyVersionCollection,
    ezp\Content\Field\LazyCollection as LazyFieldCollection,
    ezp\Content\Field\StaticCollection as StaticFieldCollection,
    ezp\Content\Search\Result,
    ezp\Content\Utils\NamePatternResolver,
    eZ\Publish\SPI\Persistence\Content as ContentValue,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\ContentId,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\LogicalOr,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\LogicalAnd,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Version as VersionValue,
    eZ\Publish\SPI\Persistence\Content\RestrictedVersion as RestrictedVersionValue,
    eZ\Publish\SPI\Persistence\ValueObject;

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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided object
     * @todo If/when we have some sort of object storage, use that to check if object is persisted instead of just id
     */
    public function create( Content $content )
    {
        if ( $content->id )
            throw new InvalidArgumentType( '$content->id', 'false' );

        $currentVersion = $content->getCurrentVersion();

        $currentVersion->notify( 'pre_create', array( 'repository' => $this->repository ) );

        $struct = new CreateStruct();
        if ( !$content->sectionId && $content->getMainLocation() )
        {
            $struct->sectionId = $content->getMainLocation()->getParent()->getContent()->sectionId;
        }

        $this->fillStruct( $struct, $content, array( 'parentLocations', 'fields', 'name' ) );
        // @todo : Take translations into account instead of hardcoding eng-GB :)
        $struct->name = array(
            'eng-GB' => $this->generateContentName( $currentVersion )
        );

        $checkCreate = true;
        foreach ( $content->getLocations() as $location )
        {
            $checkCreate = false;
            if ( !$this->repository->canUser( 'create', $content, $location->getParent() ) )
                throw new Forbidden( 'Content', 'create' );

            // @todo: Generate pathIdentificationString?
            // @todo set sort order and fields based on settings in type
            $struct->locations[] = $this->fillStruct(
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

        // Check create permissions w/o parent if no location is provided
        if ( $checkCreate && !$this->repository->canUser( 'create', $content ) )
            throw new Forbidden( 'Content', 'create' );

        foreach ( $currentVersion->getFields() as $field )
        {
            $fieldVo = $field->getState( 'properties' );
            $fieldVo->value = $field->fieldDefinition->type->toFieldValue();
            // @todo Properly implement language here instead of hardcoding eng-GB
            $fieldVo->language = 'eng-GB';
            $struct->fields[] = $fieldVo;
        }
        $vo = $this->handler->contentHandler()->create( $struct );

        $currentVersion->notify( 'post_create', array( 'repository' => $this->repository ) );

        return $this->buildDomainObject( $vo );
    }

    /**
     * Updates $content in the content repository
     *
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $version
     * @return \ezp\Content
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function update( Content $content, Version $version )
    {
        // @todo : Do any necessary actions to update $content in the content repository
        // go through all locations to create or update them?

        $version->notify( 'pre_create', array( 'repository' => $this->repository ) );

        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $content, array( "versionNo", "creatorId", "fields" ) );
        $struct->creatorId = $version->creatorId;
        $struct->versionNo = $version->versionNo;
        // @todo : Take translations into account instead of hardcoding eng-GB :)
        $struct->name = array(
            'eng-GB' => $this->generateContentName( $version )
        );

        if ( !$this->repository->canUser( 'edit', $content ) )
            throw new Forbidden( 'Content', 'edit' );

        foreach ( $version->getFields() as $field )
        {
            $fieldStruct = $field->getState( 'properties' );
            $fieldStruct->value = $field->fieldDefinition->type->toFieldValue();
            $struct->fields[] = $fieldStruct;
        }

        $do = $this->buildDomainObject(
            $this->handler->contentHandler()->update( $struct  )
        );

        $version->notify( 'post_create', array( 'repository' => $this->repository ) );

        return $do;
    }

    /**
     * Loads a content from its id ($contentId)
     *
     * @param mixed $contentId
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
     * Loads a version from its id and versionNo
     *
     * @param mixed $contentId
     * @param int|null $versionNo If null (default), then current version is returned
     * @return \ezp\Content\Version
     * @throws \ezp\Base\Exception\NotFound If content/version could not be found
     */
    public function loadVersion( $contentId, $versionNo = null )
    {
        if ( $versionNo === null )
            $contentVO = $this->handler->searchHandler()->findSingle( new ContentId( $contentId ) );
        else
            $contentVO = $this->handler->contentHandler()->load( $contentId, $versionNo );

        if ( !$contentVO instanceof ContentValue )
            throw new NotFound( 'Content\\Version', $contentId . ' ' . $versionNo  );

        $this->buildDomainObject( $contentVO, $versionDO );
        return $versionDO;
    }

    /**
     * Deletes a content from the repository
     *
     * @param \ezp\Content $content
     * @throws \ezp\Base\Exception\NotFound if content could not be found
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to remove provided object
     */
    public function delete( Content $content )
    {
        if ( !$this->repository->canUser( 'remove', $content ) )
            throw new Forbidden( 'Content', 'remove' );

        $this->handler->contentHandler()->delete( $content->id );
    }

    /**
     * List versions of a $content
     *
     * @access private For use in $content->versions, hence why it returns native array
     * @param int $contentId
     * @return \ezp\Content\Version[]
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
     * @todo Should take id + versionNo as input to avoid cyclic references, identity map is needed to make sure same
     *       Version object is used
     */
    public function loadFields( Version $version )
    {
        if ( !$version->contentId )
            throw new NotFound( 'Version->contentId', $version->contentId );
        if ( !$version->versionNo )
            throw new NotFound( 'Version->versionNo', $version->versionNo );

        if ( $version instanceof ConcreteVersion && $version->getFields() instanceof StaticFieldCollection )
            return $version->getFields()->getArrayCopy();

        $newVersion = $this->loadVersion( $version->contentId, $version->versionNo );
        $versionVo = $newVersion->getState( 'properties' );

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

                    // Make the FieldType an Observer for publish events
                    $type = $fields[$identifier]->getFieldDefinition()->getType();
                    continue 2;
                }
            }
            throw new Logic( 'field:' . $identifier, 'could not find this field in returned Version value data'  );
        }
        return $fields;
    }

    /**
     * Adds an object relation between $contentFrom and $contentTo.
     *
     * The relation created will be of type Relation::COMMON,
     *
     * @param \ezp\Content $contentFrom
     * @param \ezp\Content $contentTo
     * @param mixed|null $versionFromNo Version number for use in relation, $contentFrom->currentVersionNo used if null
     * @return \ezp\Content\Relation
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function addRelation( Content $contentFrom, Content $contentTo, $versionFromNo = null )
    {
        if ( !$this->repository->canUser( 'edit', $contentFrom ) )
            throw new Forbidden( 'Content', 'edit' );

        if ( $versionFromNo === null )
        {
            // version is in legacy system not optional, it must be derived from the content in question if not provided
            $versionFromNo = $contentFrom->currentVersionNo;
        }

        $relation = new Relation( Relation::COMMON, $contentTo );
        $struct = $this->fillStruct(
            new RelationCreateStruct(
                array(
                    'sourceContentId' => $contentFrom->id,
                    'sourceContentVersionNo' => $versionFromNo
                )
            ),
            $relation,
            array(
                'sourceFieldDefinitionId'
            )
        );
        return $relation->setState(
            array(
                "properties" => $this->handler->contentHandler()->addRelation( $struct )
            )
        );
    }

    /**
     * Removes content object relation represented by $relation.
     *
     * @param \ezp\Content\Relation $relation
     * @return void
     * @throws \ezp\Base\Exception\NotFound If the relation to be removed is not found.
     * @todo Add permission checks when contentFrom object is available
     */
    public function removeRelation( Relation $relation )
    {
        $this->handler->contentHandler()->removeRelation( $relation->id );
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
        $relations = array();
        foreach ( $this->handler->contentHandler()->loadRelations( $contentId, $version ) as $relationStruct )
        {
            $relation = new Relation(
                $relationStruct->type,
                new ProxyContent( $relationStruct->destinationContentId, $this->repository->getContentService() )
            );
            $relations[] = $relation->setState( array( 'properties' => $relationStruct ) );
        }
        return $relations;
    }

    /**
     * Copies $content in $version and returns the new copy.
     * If no version is provided, all versions will be copied
     *
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $version
     * @return \ezp\Content
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided object
     */
    public function copy( Content $content, Version $version = null )
    {
        // @todo API mistake: As parent is not provided, it will throw if user has Node/Subtree limitation
        if ( !$this->repository->canUser( 'create', $content ) )
            throw new Forbidden( 'Content', 'create' );

        $versionNo = isset( $version ) ? $version->versionNo : false;
        return $this->buildDomainObject( $this->handler->contentHandler()->copy( $content->id, $versionNo ) );
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function createDraftFromVersion( Content $content, Version $srcVersion = null )
    {
        if ( !$this->repository->canUser( 'edit', $content ) )
            throw new Forbidden( 'Content', 'edit' );

        if ( !isset( $srcVersion ) )
            $srcVersion = $content->getCurrentVersion();

        $newVersionVo = $this->handler->contentHandler()->createDraftFromVersion( $content->id, $srcVersion->versionNo );
        return $content->versions[$newVersionVo->versionNo] = $this->buildVersionDomainObject( $content, $newVersionVo );
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
     * @param bool $filterOnUserPermissions
     * @return \ezp\Content\Result
     * @todo Translation filter
     */
    public function find( Query $query, $filterOnUserPermissions = true )
    {
        if ( $filterOnUserPermissions && !$this->addUserPermissionsTo( $query ) )
        {
            // User does not have read access to content at all
            return new Result( array(), 0, $query );
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
     * @param bool $filterOnUserPermissions
     * @return \ezp\Content
     * @todo Translation filter
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to content read
     */
    public function findSingle( Query $query, $filterOnUserPermissions = true )
    {
        if ( $filterOnUserPermissions && !$this->addUserPermissionsTo( $query ) )
        {
            // User does not have read access to content at all
            throw new Forbidden( 'Content', 'read' );
        }
        // @TODO: handle $translations with $query object (not implemented yet)
        $translations = null;
        $contentVo = $this->handler->searchHandler()->findSingle(
            $query->criterion,
            $translations
        );

        return $this->buildDomainObject( $contentVo );
    }

    /**
     * Add Permissions criteria to Query if current user has read limitations
     *
     * @param \ezp\Content\Query $query
     * @throws \ezp\Base\Exception\Logic If some read limitation is missing query function
     * @return bool False if user does not have any read permissions at all
     */
    protected function addUserPermissionsTo( Query $query  )
    {
        $definition = ConcreteContent::definition();
        $limitationArray = $this->repository->getUser()->hasAccessTo( $definition['module'], 'read' );
        if ( $limitationArray === false || $limitationArray === true )
        {
            return $limitationArray;
        }

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
        return true;
    }

    /**
     * Deletes $version
     *
     * @param \ezp\Content\Version $version
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to version-remove provided object
     */
    public function deleteVersion( Version $version )
    {
        if ( !$this->repository->canUser( 'versionremove', $version->getContent() ) )
            throw new Forbidden( 'Content', 'versionremove' );

        $this->handler->contentHandler()->deleteVersion( $version->id );
    }

    /**
     * Publishes $version
     *
     * @param \ezp\Content\Version $version
     * @return \ezp\Content\Version The updated, published Version
     * @throws \ezp\Base\Exception\Logic if $version doesn't have the DRAFT status
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function publish( Version $version )
    {
        // Only drafts can be published
        if ( $version->status !== Version::STATUS_DRAFT )
        {
            throw new Logic( '$version->status', 'Version should be in Version::STATUS_DRAFT state' );
        }

        $content = $version->getContent();

        if ( !$this->repository->canUser( 'edit', $content ) )
            throw new Forbidden( 'Content', 'edit' );

        // $this->handler->beginTransaction();

        $version->notify( 'pre_publish', array( 'repository' => $this->repository ) );

        // Archive the previous version if it exists
        if ( $version->versionNo > 1 )
        {
            $this->handler->contentHandler()->setStatus( $version->contentId, Version::STATUS_ARCHIVED, $content->currentVersionNo );
            $content->versions[$content->currentVersionNo]->getState( "properties" )->status = Version::STATUS_ARCHIVED;
        }
        // @todo Maybe allow this to be part of Update struct to reduce backend calls?
        $this->handler->contentHandler()->setStatus( $version->contentId, Version::STATUS_PUBLISHED, $version->versionNo );
        $content->versions[$version->versionNo]->getState( "properties" )->status = Version::STATUS_PUBLISHED;

        // @todo: Update $content->currentVersionNo to $version->versionNo

        $updateStruct = new UpdateStruct();
        $updateStruct->id = $version->contentId;
        $updateStruct->ownerId = $version->getOwnerId();
        $updateStruct->creatorId = $version->creatorId;
        $updateStruct->versionNo = $version->versionNo;

        // @todo Make it possible to get these from the public API (set on the Content)
        // Default should still be the current time()
        $updateStruct->modified = $updateStruct->published = time();

        // @todo : Take translations into account instead of hardcoding eng-GB :)
        $updateStruct->name = array(
            'eng-GB' => $this->generateContentName( $version )
        );
        $version->getState( "properties" )->name = $updateStruct->name;

        $content->setState(
            array(
                "properties" => $this->handler->contentHandler()->publish( $updateStruct ),
                "currentVersion" => $version,
            )
        );

        $content->getState( "properties" )->currentVersionNo = $version->versionNo;

        $version->notify( 'post_publish', array( 'repository' => $this->repository ) );

        // $this->handler->commit();

        return $version;
    }

    /**
     * Generates content name for $content in $version
     *
     * @param \ezp\Content $content
     * @param \ezp\Content\Version $version
     * @return string
     * @todo Implement translations
     */
    private function generateContentName( Version $version )
    {
        $nameResolver = new NamePatternResolver( $version->getContentType()->nameSchema, $version );
        $conf = Configuration::getInstance( 'ini' );

        $length = (int)$conf->get( 'ContentSettings', 'ContentObjectNameLimit', '150' );
        if ( $length < 1 || $length > NamePatternResolver::CONTENT_NAME_MAX_LENGTH )
            $length = NamePatternResolver::CONTENT_NAME_MAX_LENGTH;
        $sequence = $conf->get( 'ContentSettings', 'ContentObjectNameLimitSequence', '...' );

        return $nameResolver->resolveNamePattern( $length, $sequence );
    }

    /**
     * Build a content Domain Object from a content Value object returned by Persistence
     * @param \eZ\Publish\SPI\Persistence\Content $vo
     * @param \ezp\Content\Version $version Will be set to version object during execution
     * @return \ezp\Content
     */
    protected function buildDomainObject( ContentValue $vo, &$version = null )
    {
        $content = new Concrete(
            new ProxyType( $vo->typeId, $this->repository->getContentTypeService() ),
            new ProxyUser( $vo->ownerId, $this->repository->getUserService() )
        );

        $content->setState(
            array(
                "section" => new ProxySection( $vo->sectionId, $this->repository->getSectionService() ),
                "properties" => $vo
            )
        );

        $version = $this->buildVersionDomainObject( $content, $vo->version );
        if ( $vo->currentVersionNo == $vo->version->versionNo )
        {
             $content->setState(
                 array(
                     "currentVersion" => $version,
                     "versions" => new LazyVersionCollection( $this, $vo->id, array( $version->versionNo => $version ) )
                 )
             );
        }
        else
        {
            $content->setState(
                 array(
                     "currentVersion" => new ProxyVersion( $content->id, $vo->currentVersionNo, $this ),
                     "versions" => new LazyVersionCollection( $this, $vo->id, array( $version->versionNo => $version ) )
                 )
             );
        }

        $locationService = $this->repository->getLocationService();
        $locations = $content->getLocations();
        foreach ( $vo->locations as $locationValue )
        {
            $locations[] = $location = new Location\Concrete( $content );
            $location->setState(
                array(
                    "properties" => $locationValue,
                    "parent" => new ProxyLocation( $locationValue->parentId, $locationService ),
                    "children" => new LazyType(
                        "ezp\\Content\\Location",
                        $locationService,
                        $location, // api uses location to be able to use sort info
                        'children'
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
     * @param \eZ\Publish\SPI\Persistence\Content\Version|\eZ\Publish\SPI\Persistence\Content\RestrictedVersion $versionVo
     * @return \ezp\Content\Version
     */
    protected function buildVersionDomainObject( Content $content, $versionVo )
    {
        if ( !$versionVo instanceof VersionValue && !$versionVo instanceof RestrictedVersionValue )
            throw new InvalidArgumentType( '$versionVo', 'Version or RestrictedVersion', $versionVo );

        $version = new ConcreteVersion( $content );

        $version->setState( array( 'properties' => $versionVo ) );

        // lazy load fields if Version does not contain fields
        if ( $versionVo instanceof RestrictedVersionValue )
        {
            $version->setState( array( 'fields' => new LazyFieldCollection( $this, $version ) ) );
            return $version;
        }

        $version->setState( array( 'fields' => new StaticFieldCollection( $version ) ) );
        // @todo Deal with translations
        foreach ( $version->getFields() as $identifier => $field )
        {
            foreach ( $versionVo->fields as $voField )
            {
                if ( $field->fieldDefinitionId == $voField->fieldDefinitionId )
                {
                    $field->setState( array( 'properties' => $voField ) );
                    $field->setValue( $voField->value->data );
                    continue 2;
                }
            }
            throw new Logic( "field:{$field->fieldDefinitionId}/" . $identifier, 'could not find this field in returned Version value data'  );
        }

        return $version;
    }

    /**
     * Defines specific conventions for filling content/update structs.
     * This method is here mainly to provide fallbacks if a struct property cannot be set in another way (aka not implemented yet)
     *
     * @todo initialLanguageId should be processed via translation management, not hardcoded
     * @see \ezp\Base\Service::setPropertyByConvention()
     */
    protected function setPropertyByConvention( ValueObject $struct, $property )
    {
        switch ( $property )
        {
            // Hardcoding this property for now, until we have proper translation management
            // See #EZPNEXT-75
            case 'initialLanguageId':
                $struct->$property = 2;
                break;

            default:
                return parent::setPropertyByConvention( $struct, $property );
        }

        return true;
    }
}
