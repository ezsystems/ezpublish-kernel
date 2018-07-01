<?php

/**
 * File containing the Siteaccess Matcher interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher as BaseMatcher;

/**
 * Interface for service based siteaccess matchers.
 */
interface Matcher extends BaseMatcher
{
    /**
     * Registers the matching configuration associated with the matcher.
     *
     * @param mixed $matchingConfiguration
     */
    public function setMatchingConfiguration($matchingConfiguration);
}
