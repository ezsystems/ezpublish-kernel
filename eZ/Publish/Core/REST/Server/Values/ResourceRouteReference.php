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

    public function __construct($route, $loadParameters)
    {
        $this->route = $route;
        $this->loadParameters = $loadParameters;
    }
}
