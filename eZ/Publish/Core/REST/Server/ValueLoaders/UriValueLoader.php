<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\ValueLoaders;

/**
 * A value object loader that uses the rest URI as an argument.
 */
interface UriValueLoader
{
    /**
     * @param string $uri REST URI to a value object. Ex: /api/ezp/v2/content/objects/1
     * @param string $mediaType The media-type to load, default if not specified. Ex: application/vnd.ez.api.Content+xml.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function load($uri, $mediaType = null);
}
