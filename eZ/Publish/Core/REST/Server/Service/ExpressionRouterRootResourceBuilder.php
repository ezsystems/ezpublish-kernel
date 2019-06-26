<?php

/**
 * File containing the ExpressionRouterRootResourceBuilder class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Service;

use eZ\Publish\Core\REST\Common\Values;
use eZ\Publish\Core\REST\Common\Values\Root;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ExpressionRouterRootResourceBuilder.
 *
 * This class builds a Root from an array building the href's using ExpressionLanguage
 * to build href's from the router or templateRouter.
 *
 * Example $resourceConfig structure:
 *
 * array(
 *      'content' => array(
 *          'mediaType' => '',
 *          'href' => 'router.generate("ezpublish_rest_listContentTypes")',
 *      ),
 *      'usersByRoleId' => array(
 *          'mediaType' => 'UserRefList',
 *          'href' => 'templateRouter.generate("ezpublish_rest_loadUsers", {roleId: "{roleId}"})',
 *      ),
 * )
 */
class ExpressionRouterRootResourceBuilder implements RootResourceBuilderInterface
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    protected $router;

    /** @var \Symfony\Component\Routing\RouterInterface */
    protected $templateRouter;

    /** @var array */
    protected $resourceConfig;

    /**
     * Creates a new resource builder.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Routing\RouterInterface $templateRouter
     * @param array $resourceConfig
     */
    public function __construct(RouterInterface $router, RouterInterface $templateRouter, array $resourceConfig)
    {
        $this->router = $router;
        $this->templateRouter = $templateRouter;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Build root resource.
     *
     * @return array|\eZ\Publish\Core\REST\Common\Values\Root
     */
    public function buildRootResource()
    {
        $language = new ExpressionLanguage();

        $resources = [];
        foreach ($this->resourceConfig as $name => $resource) {
            $resources[] = new Values\Resource(
                $name,
                $resource['mediaType'],
                $language->evaluate($resource['href'], [
                    'router' => $this->router,
                    'templateRouter' => $this->templateRouter,
                ])
            );
        }

        return new Root($resources);
    }
}
