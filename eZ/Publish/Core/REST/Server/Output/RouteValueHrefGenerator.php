<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates REST links for value object types using the router.
 */
class RouteValueHrefGenerator implements ValueHrefGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * A map of Value Object Type => Value object route.
     * Example: ['Content' => 'ezpublish_rest_loadContent'].
     *
     * @var array
     */
    private $typeRouteMap = [
        'Content' => 'ezpublish_rest_loadContent',
        'User' => 'ezpublish_rest_loadUser',
    ];

    public function __construct(RouterInterface $router, array $typeRouteMap = [])
    {
        $this->router = $router;
    }

    public function generate($type, array $parameters)
    {
        if (!isset($this->typeRouteMap[$type])) {
            throw new InvalidArgumentException('type', "No route map entry for '$type'");
        }

        try {
            return $this->router->generate($this->typeRouteMap[$type], $parameters);
        } catch (InvalidParameterException $e) {
            throw new InvalidArgumentException('type', "No route map entry for '$type'", $e);
        }
    }
}
