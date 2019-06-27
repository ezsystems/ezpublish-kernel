<?php

/**
 * File containing the UrlAliasGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Generator;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator;
use Symfony\Component\Routing\RouterInterface;

/**
 * URL generator for UrlAlias based links.
 *
 * @see \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter
 */
class UrlAliasGenerator extends Generator
{
    const INTERNAL_LOCATION_ROUTE = '_ezpublishLocation';
    const INTERNAL_CONTENT_VIEW_ROUTE = '_ez_content_view';

    /** @var \eZ\Publish\Core\Repository\Repository */
    private $repository;

    /**
     * The default router (that works with declared routes).
     *
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $defaultRouter;

    /** @var int */
    private $rootLocationId;

    /** @var array */
    private $excludedUriPrefixes = [];

    /** @var array */
    private $pathPrefixMap = [];

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /**
     * Array of characters that are potentially unsafe for output for (x)html, json, etc,
     * and respective url-encoded value.
     *
     * @var array
     */
    private $unsafeCharMap;

    public function __construct(Repository $repository, RouterInterface $defaultRouter, ConfigResolverInterface $configResolver, array $unsafeCharMap = [])
    {
        $this->repository = $repository;
        $this->defaultRouter = $defaultRouter;
        $this->configResolver = $configResolver;
        $this->unsafeCharMap = $unsafeCharMap;
    }

    /**
     * Generates the URL from $urlResource and $parameters.
     * Entries in $parameters will be added in the query string.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param array $parameters
     *
     * @return string
     */
    public function doGenerate($location, array $parameters)
    {
        $urlAliasService = $this->repository->getURLAliasService();

        $siteaccess = null;
        if (isset($parameters['siteaccess'])) {
            $siteaccess = $parameters['siteaccess'];
            unset($parameters['siteaccess']);
        }

        if ($siteaccess) {
            // We generate for a different SiteAccess, so potentially in a different language.
            $languages = $this->configResolver->getParameter('languages', null, $siteaccess);
            $urlAliases = $urlAliasService->listLocationAliases($location, false, null, null, $languages);
            // Use the target SiteAccess root location
            $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id', null, $siteaccess);
        } else {
            $languages = null;
            $urlAliases = $urlAliasService->listLocationAliases($location, false);
            $rootLocationId = $this->rootLocationId;
        }

        $queryString = '';
        unset($parameters['language'], $parameters['contentId']);
        if (!empty($parameters)) {
            $queryString = '?' . http_build_query($parameters, '', '&');
        }

        if (!empty($urlAliases)) {
            $path = $urlAliases[0]->path;
            // Remove rootLocation's prefix if needed.
            if ($rootLocationId !== null) {
                $pathPrefix = $this->getPathPrefixByRootLocationId($rootLocationId, $languages, $siteaccess);
                // "/" cannot be considered as a path prefix since it's root, so we ignore it.
                if ($pathPrefix !== '/' && ($path === $pathPrefix || mb_stripos($path, $pathPrefix . '/') === 0)) {
                    $path = mb_substr($path, mb_strlen($pathPrefix));
                } elseif ($pathPrefix !== '/' && !$this->isUriPrefixExcluded($path) && $this->logger !== null) {
                    // Location path is outside configured content tree and doesn't have an excluded prefix.
                    // This is most likely an error (from content edition or link generation logic).
                    $this->logger->warning("Generating a link to a location outside root content tree: '$path' is outside tree starting to location #$rootLocationId");
                }
            }
        } else {
            $path = $this->defaultRouter->generate(
                self::INTERNAL_CONTENT_VIEW_ROUTE,
                ['contentId' => $location->contentId, 'locationId' => $location->id]
            );
        }

        $path = $path ?: '/';

        // replace potentially unsafe characters with url-encoded counterpart
        return strtr($path . $queryString, $this->unsafeCharMap);
    }

    /**
     * Injects current root locationId that will be used for link generation.
     *
     * @param int $rootLocationId
     */
    public function setRootLocationId($rootLocationId)
    {
        $this->rootLocationId = $rootLocationId;
    }

    /**
     * @param array $excludedUriPrefixes
     */
    public function setExcludedUriPrefixes(array $excludedUriPrefixes)
    {
        $this->excludedUriPrefixes = $excludedUriPrefixes;
    }

    /**
     * Returns path corresponding to $rootLocationId.
     *
     * @param int $rootLocationId
     * @param array $languages
     * @param string $siteaccess
     *
     * @return string
     */
    public function getPathPrefixByRootLocationId($rootLocationId, $languages = null, $siteaccess = null)
    {
        if (!$rootLocationId) {
            return '';
        }

        if (!isset($this->pathPrefixMap[$siteaccess])) {
            $this->pathPrefixMap[$siteaccess] = [];
        }

        if (!isset($this->pathPrefixMap[$siteaccess][$rootLocationId])) {
            $this->pathPrefixMap[$siteaccess][$rootLocationId] = $this->repository
                ->getURLAliasService()
                ->reverseLookup(
                    $this->loadLocation($rootLocationId),
                    null,
                    false,
                    $languages
                )
                ->path;
        }

        return $this->pathPrefixMap[$siteaccess][$rootLocationId];
    }

    /**
     * Checks if passed URI has an excluded prefix, when a root location is defined.
     *
     * @param string $uri
     *
     * @return bool
     */
    public function isUriPrefixExcluded($uri)
    {
        foreach ($this->excludedUriPrefixes as $excludedPrefix) {
            $excludedPrefix = '/' . trim($excludedPrefix, '/');
            if (mb_stripos($uri, $excludedPrefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads a location by its locationId, regardless to user limitations since the router is invoked BEFORE security (no user authenticated yet).
     * Not to be used for link generation.
     *
     * @param int $locationId
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Location
     */
    public function loadLocation($locationId)
    {
        return $this->repository->sudo(
            function (Repository $repository) use ($locationId) {
                /* @var $repository \eZ\Publish\Core\Repository\Repository */
                return $repository->getLocationService()->loadLocation($locationId);
            }
        );
    }
}
