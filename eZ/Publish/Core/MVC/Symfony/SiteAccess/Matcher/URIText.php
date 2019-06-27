<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIText class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;

class URIText extends Regex implements VersatileMatcher, URILexer
{
    /** @var string */
    private $prefix;

    /** @var string */
    private $suffix;

    /**
     * Constructor.
     *
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct(array $siteAccessesConfiguration)
    {
        $this->prefix = isset($siteAccessesConfiguration['prefix']) ? $siteAccessesConfiguration['prefix'] : '';
        $this->suffix = isset($siteAccessesConfiguration['suffix']) ? $siteAccessesConfiguration['suffix'] : '';

        parent::__construct(
            '^(/' . preg_quote($this->prefix, '@') . '(\w+)' . preg_quote($this->suffix, '@') . ')',
            2
        );
    }

    public function getName()
    {
        return 'uri:text';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     */
    public function setRequest(SimplifiedRequest $request)
    {
        if (!$this->element) {
            $this->setMatchElement($request->pathinfo);
        }

        parent::setRequest($request);
    }

    /**
     * Analyses $uri and removes the siteaccess part, if needed.
     *
     * @param string $uri The original URI
     *
     * @return string The modified URI
     */
    public function analyseURI($uri)
    {
        $uri = '/' . ltrim($uri, '/');

        return preg_replace("@$this->regex@", '', $uri);
    }

    /**
     * Analyses $linkUri when generating a link to a route, in order to have the siteaccess part back in the URI.
     *
     * @param string $linkUri
     *
     * @return string The modified link URI
     */
    public function analyseLink($linkUri)
    {
        $linkUri = '/' . ltrim($linkUri, '/');
        $siteAccessUri = "/$this->prefix" . $this->match() . $this->suffix;

        return $siteAccessUri . $linkUri;
    }

    public function reverseMatch($siteAccessName)
    {
        $this->request->setPathinfo("/{$this->prefix}{$siteAccessName}{$this->suffix}{$this->request->pathinfo}");

        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
