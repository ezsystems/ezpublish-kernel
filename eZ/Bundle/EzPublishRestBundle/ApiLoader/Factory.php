<?php
namespace eZ\Bundle\EzPublishRestBundle\ApiLoader;

use eZ\Publish\Core\REST\Server\Input;
use eZ\Publish\Core\REST\Server\Output;
use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\IO\IOService;
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
        /** @var \eZ\Publish\Core\REST\Common\RequestParser $requestParser */
        $requestParser = $this->container->get( 'ezpublish_rest.request_parser' );

        /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools */
        $parserTools = $this->container->get( 'ezpublish_rest.parser_tools' );

        /** @var \eZ\Publish\Core\REST\Common\Input\FieldTypeParser $fieldTypeParser */
        $fieldTypeParser = $this->container->get( 'ezpublish_rest.field_type_parser' );

        return new Common\Input\Dispatcher(
            new Common\Input\ParsingDispatcher(
                array(
                    'application/vnd.ez.api.RoleInput'              => new Input\Parser\RoleInput( $requestParser, $this->repository->getRoleService(), $parserTools ),
                    'application/vnd.ez.api.SectionInput'           => new Input\Parser\SectionInput( $requestParser, $this->repository->getSectionService() ),
                    'application/vnd.ez.api.ContentCreate'          => new Input\Parser\ContentCreate(
                        $requestParser,
                        $this->repository->getContentService(),
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser,
                        // Needed here since there's no media type in request for embedded LocationCreate
                        ( $locationCreateParser = new Input\Parser\LocationCreate( $requestParser, $this->repository->getLocationService(), $parserTools ) ),
                        $parserTools
                    ),
                    'application/vnd.ez.api.VersionUpdate'          => new Input\Parser\VersionUpdate(
                        $requestParser,
                        $this->repository->getContentService(),
                        $fieldTypeParser
                    ),
                    'application/vnd.ez.api.UserGroupCreate'        => new Input\Parser\UserGroupCreate(
                        $requestParser,
                        $this->repository->getUserService(),
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser
                    ),
                    'application/vnd.ez.api.UserCreate'             => new Input\Parser\UserCreate(
                        $requestParser,
                        $this->repository->getUserService(),
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser,
                        $parserTools
                    ),
                    'application/vnd.ez.api.ContentUpdate'          => new Input\Parser\ContentUpdate( $requestParser ),
                    'application/vnd.ez.api.UserGroupUpdate'        => new Input\Parser\UserGroupUpdate(
                        $requestParser,
                        $this->repository->getUserService(),
                        $this->repository->getContentService(),
                        $this->repository->getLocationService(),
                        $fieldTypeParser
                    ),
                    'application/vnd.ez.api.UserUpdate'             => new Input\Parser\UserUpdate(
                        $requestParser,
                        $this->repository->getUserService(),
                        $this->repository->getContentService(),
                        $fieldTypeParser,
                        $parserTools
                    ),
                    'application/vnd.ez.api.ContentTypeGroupInput'  => new Input\Parser\ContentTypeGroupInput( $requestParser, $this->repository->getContentTypeService(), $parserTools ),
                    'application/vnd.ez.api.ContentTypeCreate'      => new Input\Parser\ContentTypeCreate(
                        $requestParser,
                        $this->repository->getContentTypeService(),
                        // Needed here since there's no media type in request for embedded FieldDefinitionCreate
                        (
                            $fieldDefinitionCreateParser = new Input\Parser\FieldDefinitionCreate(
                                $requestParser,
                                $this->repository->getContentTypeService(),
                                $fieldTypeParser,
                                $parserTools
                            )
                        ),
                        $parserTools
                    ),
                    'application/vnd.ez.api.ContentTypeUpdate'      => new Input\Parser\ContentTypeUpdate(
                        $requestParser,
                        $this->repository->getContentTypeService(),
                        $parserTools
                    ),
                    'application/vnd.ez.api.FieldDefinitionCreate'  => $fieldDefinitionCreateParser,
                    'application/vnd.ez.api.FieldDefinitionUpdate'  => new Input\Parser\FieldDefinitionUpdate(
                        $requestParser,
                        $this->repository->getContentTypeService(),
                        $fieldTypeParser,
                        $parserTools
                    ),
                    'application/vnd.ez.api.PolicyCreate'           => new Input\Parser\PolicyCreate( $requestParser, $this->repository->getRoleService(), $parserTools ),
                    'application/vnd.ez.api.PolicyUpdate'           => new Input\Parser\PolicyUpdate( $requestParser, $this->repository->getRoleService(), $parserTools ),
                    'application/vnd.ez.api.RoleAssignInput'        => new Input\Parser\RoleAssignInput( $requestParser, $parserTools ),
                    'application/vnd.ez.api.LocationCreate'         => $locationCreateParser,
                    'application/vnd.ez.api.LocationUpdate'         => new Input\Parser\LocationUpdate( $requestParser, $this->repository->getLocationService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateGroupCreate' => new Input\Parser\ObjectStateGroupCreate( $requestParser, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateGroupUpdate' => new Input\Parser\ObjectStateGroupUpdate( $requestParser, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateCreate'      => new Input\Parser\ObjectStateCreate( $requestParser, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ObjectStateUpdate'      => new Input\Parser\ObjectStateUpdate( $requestParser, $this->repository->getObjectStateService(), $parserTools ),
                    'application/vnd.ez.api.ContentObjectStates'    => new Input\Parser\ContentObjectStates( $requestParser ),
                    'application/vnd.ez.api.RelationCreate'         => new Input\Parser\RelationCreate( $requestParser ),
                    'application/vnd.ez.api.ViewInput'              => new Input\Parser\ViewInput( $requestParser ),
                    'application/vnd.ez.api.UrlWildcardCreate'      => new Input\Parser\URLWildcardCreate( $requestParser, $parserTools ),
                    'application/vnd.ez.api.UrlAliasCreate'         => new Input\Parser\URLAliasCreate( $requestParser, $parserTools ),
                    'application/vnd.ez.api.SessionInput'           => new Input\Parser\SessionInput( $requestParser, $parserTools ),

                    // internal Media-Types
                    'application/vnd.ez.api.internal.criterion.ContentId'              => new Input\Parser\Criterion\ContentId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ContentRemoteId'        => new Input\Parser\Criterion\ContentRemoteId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ContentTypeGroupId'     => new Input\Parser\Criterion\ContentTypeGroupId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ContentTypeId'          => new Input\Parser\Criterion\ContentTypeId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ContentTypeIdentifier'  => new Input\Parser\Criterion\ContentTypeIdentifier( $requestParser, $this->repository->getContentTypeService() ),
                    'application/vnd.ez.api.internal.criterion.DateMetadata'           => new Input\Parser\Criterion\DateMetadata( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.Field'                  => new Input\Parser\Criterion\Field( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.FullText'               => new Input\Parser\Criterion\FullText( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LocationId'             => new Input\Parser\Criterion\LocationId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LocationRemoteId'       => new Input\Parser\Criterion\LocationRemoteId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LogicalAnd'             => new Input\Parser\Criterion\LogicalAnd( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LogicalNot'             => new Input\Parser\Criterion\LogicalNot( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LogicalOperator'        => new Input\Parser\Criterion\LogicalOperator( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LogicalOr'              => new Input\Parser\Criterion\LogicalOr( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.MoreLikeThis'           => new Input\Parser\Criterion\MoreLikeThis( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.Operator'               => new Input\Parser\Criterion\Operator( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ParentLocationId'       => new Input\Parser\Criterion\ParentLocationId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ParentLocationRemoteId' => new Input\Parser\Criterion\ParentLocationRemoteId( $requestParser, $this->repository->getLocationService() ),
                    'application/vnd.ez.api.internal.criterion.SectionIdentifier'      => new Input\Parser\Criterion\SectionIdentifier( $requestParser, $this->repository->getSectionService() ),
                    'application/vnd.ez.api.internal.criterion.SectionId'              => new Input\Parser\Criterion\SectionId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.Status'                 => new Input\Parser\Criterion\Status( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.Subtree'                => new Input\Parser\Criterion\Subtree( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.UrlAlias'               => new Input\Parser\Criterion\UrlAlias( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.UserMetadata'           => new Input\Parser\Criterion\UserMetadata( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.ObjectStateId'          => new Input\Parser\Criterion\ObjectStateId( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.Visibility'             => new Input\Parser\Criterion\Visibility( $requestParser ),
                    'application/vnd.ez.api.internal.criterion.LanguageCode'           => new Input\Parser\Criterion\LanguageCode( $requestParser ),
                )
            ),
            array(
                'json' => new Common\Input\Handler\Json(),
                'xml'  => new Common\Input\Handler\Xml(),
            )
        );
    }

    public function getBinaryFileFieldTypeProcessor( IOService $binaryFileIOService )
    {
        $urlPrefix = $this->container->isScopeActive( 'request' ) ? $this->container->get( 'request' )->getUriForPath( '/' ) : '';

        return new FieldTypeProcessor\BinaryProcessor(
            sys_get_temp_dir(),
            $urlPrefix . $binaryFileIOService->getInternalPath( '{path}' )
        );
    }

    /**
     * Factory for ezpublish_rest.field_type_processor.ezimage
     *
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor
     */
    public function getImageFieldTypeProcessor( RequestParser $requestParser )
    {
        $configResolver = $this->container->get( 'ezpublish.config.resolver' );
        $variationsIdentifiers = array_keys( $configResolver->getParameter( 'image_variations' ) );
        sort( $variationsIdentifiers );

        return new FieldTypeProcessor\ImageProcessor(
            // Config for local temp dir
            // @todo get configuration
            sys_get_temp_dir(),
            // URL schema for image links
            // @todo get configuration
            $requestParser,
            // Image variations (names only)
            $variationsIdentifiers
        );
    }
}
