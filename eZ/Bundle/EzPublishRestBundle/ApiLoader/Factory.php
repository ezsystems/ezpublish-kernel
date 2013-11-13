<?php
namespace eZ\Bundle\EzPublishRestBundle\ApiLoader;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\IO\IOService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Routing\RouterInterface;

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
     * @param \Symfony\Component\Routing\RouterInterface $router
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor
     */
    public function getImageFieldTypeProcessor( RouterInterface $router )
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
            $router,
            // Image variations (names only)
            $variationsIdentifiers
        );
    }
}
