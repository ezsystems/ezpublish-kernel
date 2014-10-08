<?php
namespace eZ\Bundle\EzPublishRestBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class Factory
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( ConfigResolverInterface $configResolver, Repository $repository )
    {
        $this->configResolver = $configResolver;
        $this->repository = $repository;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    public function getBinaryFileFieldTypeProcessor()
    {
        $hostPrefix = isset( $this->request ) ? rtrim( $this->request->getUriForPath( '/' ), '/' ) : '';

        return new FieldTypeProcessor\BinaryProcessor( sys_get_temp_dir(), $hostPrefix );
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
        $variationsIdentifiers = array_keys( $this->configResolver->getParameter( 'image_variations' ) );
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
