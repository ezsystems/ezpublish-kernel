<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders;

use eZ\Publish\Core\REST\Server\Output\PathExpansion\Exceptions\MultipleValueLoadException;

/**
 * A decorator UriValueLoader that throws an exception when a value is loaded more than one time.
 */
class UniqueUriValueLoader implements UriValueLoader
{
    private $loadedUris = [];

    /**
     * @var UriValueLoader
     */
    private $innerLoader;

    public function __construct(UriValueLoader $innerLoader)
    {
        $this->innerLoader = $innerLoader;
    }

    /**
     * @param string $uri REST URI to a value object. Ex: /api/ezp/v2/content/objects/1
     * @param string $mediaType The media-type to load, default if not specified. Ex: application/vnd.ez.api.Content+xml.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     *
     * @throws MultipleValueLoadException When load is called on an URI that was previously loaded (at instance level).
     */
    public function load($uri, $mediaType = null)
    {
        if (isset($this->loadedUris[$uri]) && array_key_exists($mediaType, $this->loadedUris[$uri])) {
            throw new MultipleValueLoadException();
        }

        $this->loadedUris[$uri][$mediaType] = true;

        return $this->innerLoader->load($uri, $mediaType);
    }
}
