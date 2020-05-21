<?php

/**
 * File containing the DecoratedFragmentRenderer class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer;

class DecoratedFragmentRenderer implements FragmentRendererInterface, SiteAccessAware
{
    use SerializerTrait;

    /**
     * @var \Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface
     */
    private $innerRenderer;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    public function __construct(FragmentRendererInterface $innerRenderer)
    {
        $this->innerRenderer = $innerRenderer;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }

    public function setFragmentPath($path)
    {
        if (!$this->innerRenderer instanceof RoutableFragmentRenderer) {
            return null;
        }

        if ($this->siteAccess && $this->siteAccess->matcher instanceof SiteAccess\URILexer) {
            $path = $this->siteAccess->matcher->analyseLink($path);
        }

        $this->innerRenderer->setFragmentPath($path);
    }

    /**
     * Renders a URI and returns the Response content.
     *
     * @param string|ControllerReference $uri A URI as a string or a ControllerReference instance
     * @param Request $request A Request instance
     * @param array $options An array of options
     *
     * @return Response A Response instance
     */
    public function render($uri, Request $request, array $options = [])
    {
        if ($uri instanceof ControllerReference && $request->attributes->has('siteaccess')) {
            // Serialize the siteaccess to get it back after.
            // @see eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener
            $siteAccess = $request->attributes->get('siteaccess');
            $uri->attributes['serialized_siteaccess'] = json_encode($siteAccess);
            $uri->attributes['serialized_siteaccess_matcher'] = $this->getSerializer()->serialize(
                $siteAccess->matcher,
                'json'
            );
            if ($siteAccess->matcher instanceof SiteAccess\Matcher\CompoundInterface) {
                $subMatchers = $siteAccess->matcher->getSubMatchers() ?? null;
                foreach ($subMatchers as $subMatcher) {
                    $uri->attributes['serialized_siteaccess_sub_matchers'][get_class($subMatcher)] = $this->getSerializer()->serialize(
                        $subMatcher,
                        'json'
                    );
                }
            }
        }

        return $this->innerRenderer->render($uri, $request, $options);
    }

    /**
     * Gets the name of the strategy.
     *
     * @return string The strategy name
     */
    public function getName()
    {
        return $this->innerRenderer->getName();
    }
}
