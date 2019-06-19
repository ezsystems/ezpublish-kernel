<?php

/**
 * File containing the URLAlias controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\LocationService;
use Symfony\Component\HttpFoundation\Request;

/**
 * URLAlias controller.
 */
class URLAlias extends RestController
{
    /**
     * URLAlias service.
     *
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * Location service.
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller.
     *
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct(URLAliasService $urlAliasService, LocationService $locationService)
    {
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
    }

    /**
     * Returns the URL alias with the given ID.
     *
     * @param $urlAliasId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function loadURLAlias($urlAliasId)
    {
        return $this->urlAliasService->load($urlAliasId);
    }

    /**
     * Returns the list of global URL aliases.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\URLAliasRefList
     */
    public function listGlobalURLAliases()
    {
        return new Values\URLAliasRefList(
            $this->urlAliasService->listGlobalAliases(),
            $this->router->generate('ezpublish_rest_listGlobalURLAliases')
        );
    }

    /**
     * Returns the list of URL aliases for a location.
     *
     * @param $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\URLAliasRefList
     */
    public function listLocationURLAliases($locationPath, Request $request)
    {
        $locationPathParts = explode('/', $locationPath);

        $location = $this->locationService->loadLocation(
            array_pop($locationPathParts)
        );

        $custom = !($request->query->has('custom') && $request->query->get('custom') === 'false');

        return new Values\CachedValue(
            new Values\URLAliasRefList(
                $this->urlAliasService->listLocationAliases($location, $custom),
                $request->getPathInfo()
            ),
            ['locationId' => $location->id]
        );
    }

    /**
     * Creates a new URL alias.
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedURLAlias
     */
    public function createURLAlias(Request $request)
    {
        $urlAliasCreate = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        if ($urlAliasCreate['_type'] === 'LOCATION') {
            $locationPathParts = explode(
                '/',
                $this->requestParser->parseHref($urlAliasCreate['location']['_href'], 'locationPath')
            );

            $location = $this->locationService->loadLocation(
                array_pop($locationPathParts)
            );

            try {
                $createdURLAlias = $this->urlAliasService->createUrlAlias(
                    $location,
                    $urlAliasCreate['path'],
                    $urlAliasCreate['languageCode'],
                    $urlAliasCreate['forward'],
                    $urlAliasCreate['alwaysAvailable']
                );
            } catch (InvalidArgumentException $e) {
                throw new ForbiddenException($e->getMessage());
            }
        } else {
            try {
                $createdURLAlias = $this->urlAliasService->createGlobalUrlAlias(
                    $urlAliasCreate['resource'],
                    $urlAliasCreate['path'],
                    $urlAliasCreate['languageCode'],
                    $urlAliasCreate['forward'],
                    $urlAliasCreate['alwaysAvailable']
                );
            } catch (InvalidArgumentException $e) {
                throw new ForbiddenException($e->getMessage());
            }
        }

        return new Values\CreatedURLAlias(
            [
                'urlAlias' => $createdURLAlias,
            ]
        );
    }

    /**
     * The given URL alias is deleted.
     *
     * @param $urlAliasId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteURLAlias($urlAliasId)
    {
        $this->urlAliasService->removeAliases(
            [
                $this->urlAliasService->load($urlAliasId),
            ]
        );

        return new Values\NoContent();
    }
}
