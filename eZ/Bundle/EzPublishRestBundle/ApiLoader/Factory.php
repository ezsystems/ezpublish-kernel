<?php
namespace eZ\Bundle\EzPublishRestBundle\ApiLoader;

use eZ\Publish\Core\REST\Server\Input;
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
        $urlHandler = $this->container->get( 'ezpublish_rest.url_handler' );
        $parserTools = $this->container->get( 'ezpublish_rest.parser_tools' );
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
                        // Needed here since there's no ContentType in request for embedded LocationCreate
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
}
