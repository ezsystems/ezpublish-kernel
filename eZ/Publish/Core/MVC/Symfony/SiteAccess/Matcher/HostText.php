<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostText class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher;

class HostText extends Regex implements VersatileMatcher
{
    private $prefix;

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
            '^' . preg_quote($this->prefix, '@') . "(\w+)" . preg_quote($this->suffix, '@') . '$',
            1
        );
    }

    public function getName()
    {
        return 'host:text';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     */
    public function setRequest(SimplifiedRequest $request)
    {
        if (!$this->element) {
            $this->setMatchElement($request->host);
        }

        parent::setRequest($request);
    }

    public function reverseMatch($siteAccessName)
    {
        $this->request->setHost($this->prefix . $siteAccessName . $this->suffix);

        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
