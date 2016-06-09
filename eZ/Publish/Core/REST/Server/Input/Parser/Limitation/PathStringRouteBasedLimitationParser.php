<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Limitation;

use eZ\Publish\API\Repository\Values;

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
     * Prefixes the value parsed by the parent with a '/', and ensures it also ends with a '/'.
     *
     * @param $limitationValue
     *
     * @return false|mixed
     */
    protected function parseIdFromHref($limitationValue)
    {
        return '/' . trim(parent::parseIdFromHref($limitationValue), '/') . '/';
    }
}
