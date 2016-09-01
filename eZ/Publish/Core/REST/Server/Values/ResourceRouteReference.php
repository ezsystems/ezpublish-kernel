<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * A reference to a REST resource route.
 *
 * @property string $route
 * @property array $loadParameters
 * @property string $mediaTypeName
 */
class ResourceRouteReference extends ValueObject
{
    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $loadParameters;

    /**
     * The media-type name (ContentInfo, Location) of the resource. If null, the default will be used.
     * @var null
     */
    protected $mediaTypeName;

    /**
     * ResourceRouteReference constructor.
     * @param array $route
     * @param $loadParameters
     * @param string $mediaType The media-type name (ContentInfo, Location) of the resource. If null, the default will be used.
     */
    public function __construct($route, $loadParameters, $mediaType = null)
    {
        $this->route = $route;
        $this->loadParameters = $loadParameters;
        $this->mediaTypeName = $mediaType;
    }
}
