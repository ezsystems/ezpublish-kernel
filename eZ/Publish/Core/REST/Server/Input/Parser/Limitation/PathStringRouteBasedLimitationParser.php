<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Limitation;

use eZ\Publish\API\Repository\Values;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Generic limitation value parser.
 *
 * Instances are built with:
 * - The name of a route parameter, that will be searched for limitation values
 *   Example: "sectionId" from "/content/section/{sectionId}"
 * - The FQN of the limitation value object that the parser builds
 */
class PathStringRouteBasedLimitationParser extends RouteBasedLimitationParser
{
    /**
     * Prefixes the value parsed by the parent with a '/'.
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser if the '_href' attribute doesn't end with a slash, since 6.4
     *
     * @param $limitationValue
     *
     * @return false|mixed
     */
    protected function parseIdFromHref($limitationValue)
    {
        if (substr($limitationValue['_href'], -1) !== '/') {
            throw new Exceptions\Parser("The '_href' attribute must end with a slash.");
        }

        return '/' . ltrim(parent::parseIdFromHref($limitationValue), '/');
    }
}
