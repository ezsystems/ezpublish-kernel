<?php

namespace eZ\Bundle\EzPublishRestBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\RequestStackAware;
use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Routing\RouterInterface;

class Factory
{
    use RequestStackAware;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(ConfigResolverInterface $configResolver, Repository $repository)
    {
        $this->configResolver = $configResolver;
        $this->repository = $repository;
    }

    public function getBinaryFileFieldTypeProcessor()
    {
        $request = $this->getCurrentRequest();
        $hostPrefix = isset($request) ? rtrim($request->getUriForPath('/'), '/') : '';

        return new FieldTypeProcessor\BinaryProcessor(sys_get_temp_dir(), $hostPrefix);
    }

    public function getMediaFieldTypeProcessor()
    {
        return new FieldTypeProcessor\MediaProcessor(sys_get_temp_dir());
    }

    /**
     * Factory for ezpublish_rest.field_type_processor.ezimage.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\ImageProcessor
     */
    public function getImageFieldTypeProcessor(RouterInterface $router)
    {
        $variationsIdentifiers = array_keys($this->configResolver->getParameter('image_variations'));
        sort($variationsIdentifiers);

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
