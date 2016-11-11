<?php

/**
 * File containing the MatcherBuilderInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

interface MatcherBuilderInterface
{
    /**
     * Builds siteaccess matcher.
     *
     * @param string $matcherIdentifier "Identifier" of the matcher to build (i.e. its FQ class name).
     * @param mixed $matchingConfiguration Configuration to pass to the matcher. Can be anything the matcher supports.
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request The request to match against.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher
     *
     * @throws \RuntimeException
     */
    public function buildMatcher($matcherIdentifier, $matchingConfiguration, SimplifiedRequest $request);
}
