<?php

/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\Host class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

/**
 * @deprecated since 5.3 as it cannot be reverted.
 */
class Host extends Regex implements Matcher
{
    /**
     * Constructor.
     *
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct(array $siteAccessesConfiguration)
    {
        parent::__construct(
            isset($siteAccessesConfiguration['regex']) ? $siteAccessesConfiguration['regex'] : '',
            isset($siteAccessesConfiguration['itemNumber']) ? (int)$siteAccessesConfiguration['itemNumber'] : 1
        );
    }

    public function getName()
    {
        return 'host:regexp';
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
}
