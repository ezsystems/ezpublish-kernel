<?php
namespace eZ\Bundle\EzPublishRestBundle\ApiLoader;

use eZ\Publish\Core\REST\Server\Input;
use eZ\Publish\Core\REST\Server\Output;
use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use eZ\Publish\Core\REST\Server\FieldTypeProcessor;
use eZ\Publish\Core\REST\Common;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\API\Repository\Repository;

class Factory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( ContainerInterface $container, Repository $repository )
    {
        $this->container = $container;
        $this->repository = $repository;
    }

    public function buildInputDispatcher()
    {
        /** @var \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler */
        $urlHandler = $this->container->get( 'ezpublish_rest.url_handler' );

        /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools */
        $parserTools = $this->container->get( 'ezpublish_rest.parser_tools' );

        /** @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser */
        $fieldTypeParser = $this->container->get( 'ezpublish_rest.field_type_parser' );

        return new Common\Input\Dispatcher(
            new Common\Input\ParsingDispatcher(
                array(
                    'application/vnd.ez.api.RoleInput'              => new Input\Parser\RoleInput( $urlHandler, $this->repository->getRoleService(), $parserTools ),
                    'application/vnd.ez.api.SectionInput'           => new Input\Parser\SectionInput( $urlHandler, $this->repository->getSectionService() ),
                    'application/vnd.ez.api.ContentCreate'          => new Input\Parser\ContentCreate(
                        $urlHandler,
                        $this->repository->getContentService(),
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser,
                        // Needed here since there's no media type in request for embedded LocationCreate
                        ( $locationCreateParser = new Input\Parser\LocationCreate( $urlHandler, $this->repository->getLocationService(), $parserTools ) ),
                        $parserTools
                    ),
                    'application/vnd.ez.api.VersionUpdate'          => new Input\Parser\VersionUpdate(
                        $urlHandler,
                        $this->repository->getContentService(),
                        $fieldTypeParser
                    ),
                    'application/vnd.ez.api.UserGroupCreate'        => new Input\Parser\UserGroupCreate(
                        $urlHandler,
                        $this->repository->getUserService(),
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser
                    ),
                    'application/vnd.ez.api.UserCreate'             => new Input\Parser\UserCreate(
                        $urlHandler,
                        $this->repository->getUserService(),
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser,
                        $parserTools
                    ),
                    'application/vnd.ez.api.ContentUpdate'          => new Input\Parser\ContentUpdate( $urlHandler ),
                    'application/vnd.ez.api.UserGroupUpdate'        => new Input\Parser\UserGroupUpdate(
                        $urlHandler,
                        $this->repository->getUserService(),
                        $this->repository->getContentService(),
                        $this->repository->getLocationService(),
                        $fieldTypeParser
                    ),
                    'application/vnd.ez.api.UserUpdate'             => new Input\Parser\UserUpdate(
                        $urlHandler,
                        $this->repository->getUserService(),
                        $this->repository->getContentService(),
                        $fieldTypeParser,
                        $parserTools
                    ),
                    'application/vnd.ez.api.ContentTypeCreate'      => new Input\Parser\ContentTypeCreate(
                        $urlHandler,
                        $this->repository->getContentTypeService(),
                        // Needed here since there's no media type in request for embedded FieldDefinitionCreate
                        ( $fieldDefinitionCreateParser = new Input\Parser\FieldDefinitionCreate( $urlHandler, $this->repository->getContentTypeService(), $parserTools ) ),
                        $parserTools
                    ),
                    'application/vnd.ez.api.ContentTypeUpdate'      => new Input\Parser\ContentTypeUpdate(
                        $urlHandler,
                        $this->repository->getContentTypeService(),
                        $parserTools
                    ),
                    'application/vnd.ez.api.FieldDefinitionCreate'  => $fieldDefinitionCreateParser,
                    'application/vnd.ez.api.FieldDefinitionUpdate'  => new Input\Parser\FieldDefinitionUpdate(
                        $urlHandler,
                        $this->repository->getContentTypeService(),
                        $parserTools
                    ),
                    'application/vnd.ez.api.PolicyCreate'           => new Input\Parser\PolicyCreate( $urlHandler, $this->repository->getRoleService(), $parserTools ),
                    'application/vnd.ez.api.PolicyUpdate'           => new Input\Parser\PolicyUpdate( $urlHandler, $this->repository->getRoleService(), $parserTools ),
                    'application/vnd.ez.api.RoleAssignInput'        => new Input\Parser\RoleAssignInput( $urlHandler, $parserTools ),
                    'application/vnd.ez.api.LocationCreate'         => $locationCreateParser,
                    'application/vnd.ez.api.LocationUpdate'         => new Input\Parser\LocationUpdate( $urlHandler, $this->repository->getLocationService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateGroupCreate' => new Input\Parser\ObjectStateGroupCreate( $urlHandler, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateGroupUpdate' => new Input\Parser\ObjectStateGroupUpdate( $urlHandler, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateCreate'      => new Input\Parser\ObjectStateCreate( $urlHandler, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateUpdate'      => new Input\Parser\ObjectStateUpdate( $urlHandler, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ContentObjectStates'    => new Input\Parser\ContentObjectStates( $urlHandler ),
                    'application/vnd.ez.api.RelationCreate'         => new Input\Parser\RelationCreate( $urlHandler ),
                    'application/vnd.ez.api.ViewInput'              => new Input\Parser\ViewInput( $urlHandler ),

                    // internal Media-Types
                    'application/vnd.ez.api.internal.criterion.ContentId'              => new Input\Parser\Criterion\ContentId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.ContentRemoteId'        => new Input\Parser\Criterion\ContentRemoteId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.ContentTypeGroupId'     => new Input\Parser\Criterion\ContentTypeGroupId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.ContentTypeId'          => new Input\Parser\Criterion\ContentTypeId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.ContentTypeIdentifier'  => new Input\Parser\Criterion\ContentTypeIdentifier( $urlHandler, $this->repository->getContentTypeService() ),
                    'application/vnd.ez.api.internal.criterion.DateMetadata'           => new Input\Parser\Criterion\DateMetadata( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.Field'                  => new Input\Parser\Criterion\Field( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.FullText'               => new Input\Parser\Criterion\FullText( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.LocationId'             => new Input\Parser\Criterion\LocationId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.LocationRemoteId'       => new Input\Parser\Criterion\LocationRemoteId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.LogicalAnd'             => new Input\Parser\Criterion\LogicalAnd( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.LogicalNot'             => new Input\Parser\Criterion\LogicalNot( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.LogicalOperator'        => new Input\Parser\Criterion\LogicalOperator( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.LogicalOr'              => new Input\Parser\Criterion\LogicalOr( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.MoreLikeThis'           => new Input\Parser\Criterion\MoreLikeThis( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.Operator'               => new Input\Parser\Criterion\Operator( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.ParentLocationId'       => new Input\Parser\Criterion\ParentLocationId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.ParentLocationRemoteId' => new Input\Parser\Criterion\ParentLocationRemoteId( $urlHandler, $this->repository->getLocationService() ),
                    'application/vnd.ez.api.internal.criterion.SectionIdentifier'      => new Input\Parser\Criterion\SectionIdentifier( $urlHandler, $this->repository->getSectionService() ),
                    'application/vnd.ez.api.internal.criterion.SectionId'              => new Input\Parser\Criterion\SectionId( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.Status'                 => new Input\Parser\Criterion\Status( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.Subtree'                => new Input\Parser\Criterion\Subtree( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.UrlAlias'               => new Input\Parser\Criterion\UrlAlias( $urlHandler ),
                    'application/vnd.ez.api.internal.criterion.UserMetadata'           => new Input\Parser\Criterion\UserMetadata( $urlHandler ),
                )
            ),
            array(
                'json' => new Common\Input\Handler\Json(),
                'xml'  => new Common\Input\Handler\Xml(),
            )
        );
    }

    public function buildFieldTypeProcessorRegistry()
    {
        return new Common\FieldTypeProcessorRegistry(
            array(
                'ezimage' => new FieldTypeProcessor\ImageProcessor(
                    // Config for local temp dir
                    // @todo get configuration
                    sys_get_temp_dir(),
                    // URL schema for image links
                    // @todo get configuration
                    'http://example.com/fancy_site/{variant}/images/{path}',
                    // Image variants (names only)
                    // @todo get configuration
                    array(
                        'original' => 'image/jpeg',
                        'gallery' => 'image/jpeg',
                        'thumbnail' => 'image/png',
                    )
                )
            )
        );
    }

    public function buildResponseVisitorDispatcher(
        Common\URLHandler $urlHandler,
        Common\Output\FieldTypeSerializer $fieldTypeSerializer,
        Repository $repository )
    {
        $valueObjectVisitors = array(
            // Errors

            '\\eZ\\Publish\\API\\Repository\\Exceptions\\InvalidArgumentException'   => new Output\ValueObjectVisitor\InvalidArgumentException( $urlHandler,  true ),
            '\\eZ\\Publish\\API\\Repository\\Exceptions\\NotFoundException'          => new Output\ValueObjectVisitor\NotFoundException( $urlHandler,  true ),
            '\\eZ\\Publish\\API\\Repository\\Exceptions\\UnauthorizedException'      => new Output\ValueObjectVisitor\UnauthorizedException( $urlHandler,  true ),
            '\\eZ\\Publish\\API\\Repository\\Exceptions\\BadStateException'          => new Output\ValueObjectVisitor\BadStateException( $urlHandler,  true ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Exceptions\\BadRequestException'     => new Output\ValueObjectVisitor\BadRequestException( $urlHandler,  true ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Exceptions\\ForbiddenException'      => new Output\ValueObjectVisitor\ForbiddenException( $urlHandler,  true ),
            '\\Exception'                                                            => new Output\ValueObjectVisitor\Exception( $urlHandler,  true ),

            // Section

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\SectionList'                 => new Output\ValueObjectVisitor\SectionList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedSection'              => new Output\ValueObjectVisitor\CreatedSection( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section'               => new Output\ValueObjectVisitor\Section( $urlHandler ),

            // URLWildcard

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\URLWildcardList'             => new Output\ValueObjectVisitor\URLWildcardList( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\URLWildcard'           => new Output\ValueObjectVisitor\URLWildcard( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedURLWildcard'          => new Output\ValueObjectVisitor\CreatedURLWildcard( $urlHandler ),

            // Content

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ContentList'                 => new Output\ValueObjectVisitor\ContentList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestContent'                 => new Output\ValueObjectVisitor\RestContent( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedContent'              => new Output\ValueObjectVisitor\CreatedContent( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\VersionList'                 => new Output\ValueObjectVisitor\VersionList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedVersion'              => new Output\ValueObjectVisitor\CreatedVersion( $urlHandler, $fieldTypeSerializer ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo'           => new Output\ValueObjectVisitor\VersionInfo( $urlHandler ),

            // The following two visitors are quite similar, as they both generate
            // <Version> resource. However, "Version" visitor DOES NOT generate embedded
            // <Fields> and <Relations> elements, while "Content" visitor DOES
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\Version'                     => new Output\ValueObjectVisitor\Version( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content'               => new Output\ValueObjectVisitor\Content(
                $urlHandler,
                $fieldTypeSerializer
            ),

            // UserGroup

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestUserGroup'               => new Output\ValueObjectVisitor\RestUserGroup( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedUserGroup'            => new Output\ValueObjectVisitor\CreatedUserGroup( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\UserGroupList'               => new Output\ValueObjectVisitor\UserGroupList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\UserGroupRefList'            => new Output\ValueObjectVisitor\UserGroupRefList( $urlHandler ),

            // User

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\UserList'                    => new Output\ValueObjectVisitor\UserList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\UserRefList'                 => new Output\ValueObjectVisitor\UserRefList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedUser'                 => new Output\ValueObjectVisitor\CreatedUser( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestUser'                    => new Output\ValueObjectVisitor\RestUser( $urlHandler ),

            // ContentType

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestContentType'             => new Output\ValueObjectVisitor\RestContentType( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedContentType'          => new Output\ValueObjectVisitor\CreatedContentType( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ContentTypeList'             => new Output\ValueObjectVisitor\ContentTypeList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ContentTypeInfoList'         => new Output\ValueObjectVisitor\ContentTypeInfoList( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup'  => new Output\ValueObjectVisitor\ContentTypeGroup( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedContentTypeGroup'     => new Output\ValueObjectVisitor\CreatedContentTypeGroup( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ContentTypeGroupList'        => new Output\ValueObjectVisitor\ContentTypeGroupList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ContentTypeGroupRefList'     => new Output\ValueObjectVisitor\ContentTypeGroupRefList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\FieldDefinitionList'         => new Output\ValueObjectVisitor\FieldDefinitionList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedFieldDefinition'      => new Output\ValueObjectVisitor\CreatedFieldDefinition(
                $urlHandler,
                $fieldTypeSerializer
            ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestFieldDefinition'         => new Output\ValueObjectVisitor\RestFieldDefinition(
                $urlHandler,
                $fieldTypeSerializer
            ),

            // Relation

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RelationList'                => new Output\ValueObjectVisitor\RelationList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestRelation'                => new Output\ValueObjectVisitor\RestRelation( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedRelation'             => new Output\ValueObjectVisitor\CreatedRelation( $urlHandler ),

            // Role

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RoleList'                    => new Output\ValueObjectVisitor\RoleList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedRole'                 => new Output\ValueObjectVisitor\CreatedRole( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Role'                     => new Output\ValueObjectVisitor\Role( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\Policy'                   => new Output\ValueObjectVisitor\Policy( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedPolicy'               => new Output\ValueObjectVisitor\CreatedPolicy( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\PolicyList'                  => new Output\ValueObjectVisitor\PolicyList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RoleAssignmentList'          => new Output\ValueObjectVisitor\RoleAssignmentList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestUserRoleAssignment'      => new Output\ValueObjectVisitor\RestUserRoleAssignment( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestUserGroupRoleAssignment' => new Output\ValueObjectVisitor\RestUserGroupRoleAssignment( $urlHandler ),

            // Location

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedLocation'             => new Output\ValueObjectVisitor\CreatedLocation( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location'              => new Output\ValueObjectVisitor\Location( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\LocationList'                => new Output\ValueObjectVisitor\LocationList( $urlHandler ),

            // Trash

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\Trash'                       => new Output\ValueObjectVisitor\Trash( $urlHandler ),
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\TrashItem'             => new Output\ValueObjectVisitor\TrashItem( $urlHandler ),

            // Views

            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestExecutedView'            => new Output\ValueObjectVisitor\RestExecutedView(
                $urlHandler,
                $repository->getLocationService(),
                $repository->getContentService()
            ),

            // Object state

            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup'  => new Output\ValueObjectVisitor\ObjectStateGroup( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedObjectStateGroup'     => new Output\ValueObjectVisitor\CreatedObjectStateGroup( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ObjectStateGroupList'        => new Output\ValueObjectVisitor\ObjectStateGroupList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Common\\Values\\RestObjectState'             => new Output\ValueObjectVisitor\RestObjectState( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\CreatedObjectState'          => new Output\ValueObjectVisitor\CreatedObjectState( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ObjectStateList'             => new Output\ValueObjectVisitor\ObjectStateList( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Common\\Values\\ContentObjectStates'         => new Output\ValueObjectVisitor\ContentObjectStates( $urlHandler ),

            // REST specific
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\TemporaryRedirect'           => new Output\ValueObjectVisitor\TemporaryRedirect( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\PermanentRedirect'           => new Output\ValueObjectVisitor\PermanentRedirect( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ResourceDeleted'             => new Output\ValueObjectVisitor\ResourceDeleted( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ResourceCreated'             => new Output\ValueObjectVisitor\ResourceCreated( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\ResourceSwapped'             => new Output\ValueObjectVisitor\ResourceSwapped( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\NoContent'                   => new Output\ValueObjectVisitor\NoContent( $urlHandler ),
            '\\eZ\\Publish\\Core\\REST\\Common\\Values\\Root'                        => new Output\ValueObjectVisitor\Root( $urlHandler ),
        );

        $jsonVisitor = new Common\Output\Visitor(
            new Common\Output\Generator\Json(
                new Common\Output\Generator\Json\FieldTypeHashGenerator()
            ),
            $valueObjectVisitors
        );

        $xmlVisitor = new Common\Output\Visitor(
            new Common\Output\Generator\Xml(
                new Common\Output\Generator\Xml\FieldTypeHashGenerator()
            ),
            $valueObjectVisitors
        );

        $acceptHeaderVisitorMapping = array(
            '(^application/vnd\\.ez\\.api\\.[A-Za-z]+\\+json$)' =>  $jsonVisitor,
            '(^application/vnd\\.ez\\.api\\.[A-Za-z]+\\+xml$)'  => $xmlVisitor,
            '(^application/json$)'  => $jsonVisitor,
            '(^application/xml$)'  => $xmlVisitor,
            // '(^.*/.*$)'  => new View\InvalidApiUse(),
            // Fall back gracefully to XML visiting. Also helps support responses
            // without Accept header (e.g. DELETE requests).
            '(^.*/.*$)'  => $xmlVisitor,
        );

        return new AcceptHeaderVisitorDispatcher( $acceptHeaderVisitorMapping );
    }
}
