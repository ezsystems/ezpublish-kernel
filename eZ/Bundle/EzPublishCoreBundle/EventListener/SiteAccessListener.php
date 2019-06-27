<?php

/**
 * File containing the SiteAccessListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SiteAccess match listener.
 */
class SiteAccessListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteaccess;

    public function __construct(
        SiteAccess $siteaccess
    ) {
        $this->siteaccess = $siteaccess;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MVCEvents::SITEACCESS => ['onSiteAccessMatch', 255],
        ];
    }

    public function onSiteAccessMatch(PostSiteAccessMatchEvent $event): void
    {
        $request = $event->getRequest();
        $matchedSiteAccess = $event->getSiteAccess();

        $siteAccess = $this->siteaccess;
        $siteAccess->name = $matchedSiteAccess->name;
        $siteAccess->matchingType = $matchedSiteAccess->matchingType;
        $siteAccess->matcher = $matchedSiteAccess->matcher;

        // We already have semanticPathinfo (sub-request)
        if ($request->attributes->has('semanticPathinfo')) {
            $vpString = $request->attributes->get('viewParametersString');
            if (!empty($vpString)) {
                $request->attributes->set(
                    'viewParameters',
                    $this->generateViewParametersArray($vpString)
                );
            } else {
                $request->attributes->set('viewParametersString', '');
                $request->attributes->set('viewParameters', []);
            }

            return;
        }

        // Analyse the pathinfo if needed since it might contain the siteaccess (i.e. like in URI mode)
        $pathinfo = rawurldecode($request->getPathInfo());
        if ($siteAccess->matcher instanceof URILexer) {
            $semanticPathinfo = $siteAccess->matcher->analyseURI($pathinfo);
        } else {
            $semanticPathinfo = $pathinfo;
        }

        // Get view parameters and cleaned up pathinfo (without view parameters string)
        list($semanticPathinfo, $viewParameters, $viewParametersString) = $this->getViewParameters($semanticPathinfo);

        // Storing the modified pathinfo in 'semanticPathinfo' request attribute, to keep a trace of it.
        // Routers implementing RequestMatcherInterface should thus use this attribute instead of the original pathinfo
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $request->attributes->set('viewParameters', $viewParameters);
        $request->attributes->set('viewParametersString', $viewParametersString);
    }

    /**
     * Extracts view parameters from $pathinfo.
     * In the pathinfo, view parameters are in the form /(param_name)/param_value.
     *
     * @param string $pathinfo
     *
     * @return array First element is the cleaned up pathinfo (without the view parameters string).
     *               Second element is the view parameters hash.
     *               Third element is the view parameters string (e.g. /(foo)/bar)
     */
    private function getViewParameters(string $pathinfo): array
    {
        // No view parameters, get out of here.
        if (($vpStart = strpos($pathinfo, '/(')) === false) {
            return [$pathinfo, [], ''];
        }

        $vpString = substr($pathinfo, $vpStart + 1);
        $viewParameters = $this->generateViewParametersArray($vpString);

        // Now remove the view parameters string from $semanticPathinfo
        $pathinfo = substr($pathinfo, 0, $vpStart);

        return [$pathinfo, $viewParameters, "/$vpString"];
    }

    /**
     * Generates the view parameters array from the view parameters string.
     *
     * @param $vpString
     *
     * @return array
     */
    private function generateViewParametersArray(string $vpString): array
    {
        $vpString = trim($vpString, '/');
        $viewParameters = [];

        $vpSegments = explode('/', $vpString);
        for ($i = 0, $iMax = count($vpSegments); $i < $iMax; ++$i) {
            if (empty($vpSegments[$i])) {
                continue;
            }

            // View parameter name.
            // We extract it + the value from the following segment (next element in $vpSegments array)
            if ($vpSegments[$i][0] === '(') {
                $paramName = str_replace(['(', ')'], '', $vpSegments[$i]);
                // A value is present (e.g. /(foo)/bar)
                if (isset($vpSegments[$i + 1])) {
                    $viewParameters[$paramName] = $vpSegments[$i + 1];
                    unset($vpSegments[$i + 1]);
                } else {
                    // No value (e.g. /(foo)) => set it to empty string
                    $viewParameters[$paramName] = '';
                }
            } elseif (isset($paramName)) {
                // Orphan segment (no previous parameter name), e.g. /(foo)/bar/baz
                // Add it to the previous parameter.
                $viewParameters[$paramName] .= '/' . $vpSegments[$i];
            }
        }

        return $viewParameters;
    }
}
