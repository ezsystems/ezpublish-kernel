<?php

/**
 * File containing the HttpUtils class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils as BaseHttpUtils;

class HttpUtils extends BaseHttpUtils implements SiteAccessAware
{
    /** @var SiteAccess */
    private $siteAccess;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }

    private function analyzeLink($path)
    {
        if ($path[0] === '/' && $this->siteAccess->matcher instanceof SiteAccess\URILexer) {
            $path = $this->siteAccess->matcher->analyseLink($path);
        }

        return $path;
    }

    public function generateUri($request, $path)
    {
        if ($this->isRouteName($path)) {
            // Remove siteaccess attribute to avoid triggering reverse siteaccess lookup during link generation.
            $request->attributes->remove('siteaccess');
        }

        return parent::generateUri($request, $this->analyzeLink($path));
    }

    public function checkRequestPath(Request $request, $path)
    {
        return parent::checkRequestPath($request, $this->analyzeLink($path));
    }

    /**
     * @param string $path Path can be URI, absolute URL or a route name.
     *
     * @return bool
     */
    private function isRouteName($path)
    {
        return $path && strpos($path, 'http') !== 0 && strpos($path, '/') !== 0;
    }
}
