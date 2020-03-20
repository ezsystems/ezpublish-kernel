<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\TwigFunction;

class RoutingExtension extends AbstractExtension
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface */
    private $routeReferenceGenerator;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(
        RouteReferenceGeneratorInterface $routeReferenceGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->routeReferenceGenerator = $routeReferenceGenerator;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_route',
                [$this, 'getRouteReference']
            ),
            new TwigFunction(
                'ez_path',
                [$this, 'getPath'],
                ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]
            ),
            new TwigFunction(
                'ez_url',
                [$this, 'getUrl'],
                ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]
            ),
        ];
    }

    public function getName(): string
    {
        return 'ezpublish.routing';
    }

    /**
     * @param mixed $resource
     * @param array $params
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference
     */
    public function getRouteReference($resource = null, $params = []): RouteReference
    {
        return $this->routeReferenceGenerator->generate($resource, $params);
    }

    public function getPath($name, array $parameters = [], bool $relative = false): string
    {
        $referenceType = $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;
        if (is_object($name)) {
            return $this->generateUrlForObject($name, $parameters, $referenceType);
        }

        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    public function getUrl($name, array $parameters = [], bool $schemeRelative = false): string
    {
        $referenceType = $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;
        if (is_object($name)) {
            return $this->generateUrlForObject($name, $parameters, $referenceType);
        }

        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function generateUrlForObject(object $object, array $parameters, int $referenceType): string
    {
        if ($object instanceof Location) {
            $routeName = UrlAliasRouter::URL_ALIAS_ROUTE_NAME;
            $parameters += [
                'locationId' => $object->id,
            ];
        } elseif ($object instanceof Content || $object instanceof ContentInfo) {
            $routeName = UrlAliasRouter::URL_ALIAS_ROUTE_NAME;
            $parameters += [
                'contentId' => $object->id,
            ];
        } elseif ($object instanceof RouteReference) {
            $routeName = $object->getRoute();
            $parameters += $object->getParams();
        } else {
            $routeName = '';
            $parameters += [
                RouteObjectInterface::ROUTE_OBJECT => $$object,
            ];
        }

        return $this->urlGenerator->generate($routeName, $parameters, $referenceType);
    }

    /**
     * Determines at compile time whether the generated URL will be safe and thus
     * saving the unneeded automatic escaping for performance reasons.
     *
     * @see \Symfony\Bridge\Twig\Extension\RoutingExtension::isUrlGenerationSafe
     */
    public function isUrlGenerationSafe(Node $argsNode): array
    {
        // support named arguments
        $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
            $argsNode->hasNode('1') ? $argsNode->getNode('1') : null
        );

        if (null === $paramsNode || $paramsNode instanceof ArrayExpression && \count($paramsNode) <= 2 &&
            (!$paramsNode->hasNode('1') || $paramsNode->getNode('1') instanceof ConstantExpression)
        ) {
            return ['html'];
        }

        return [];
    }
}
