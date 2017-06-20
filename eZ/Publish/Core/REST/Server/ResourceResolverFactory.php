<?php

namespace eZ\Publish\Core\REST\Server;

use eZ\Publish\Core\REST\Common\RequestParser;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\UserService;

class ResourceResolverFactory
{
    private $requestParser;

    private $contentTypeService;

    private $urlAliasService;

    private $sectionService;

    private $userService;

    /**
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\UrlAliasService $urlAliasService
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     * @param \eZ\Publish\API\Repository\UserService $userService
     */
    public function __construct(
        RequestParser $requestParser,
        ContentTypeService $contentTypeService,
        UrlAliasService $urlAliasService,
        SectionService $sectionService,
        UserService $userService
    ) {
        $this->requestParser = $requestParser;

        $this->contentTypeService = $contentTypeService;
        $this->urlAliasService = $urlAliasService;
        $this->sectionService = $sectionService;
        $this->userService = $userService;
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\ResourceResolver
     */
    public function createResolver()
    {
        $contentTypeService = $this->contentTypeService;
        $urlAliasService = $this->urlAliasService;
        $sectionService = $this->sectionService;
        $userService = $this->userService;

        return new ResourceResolver(
            $this->requestParser,
            [
                'type' => function ($uriParameters) use ($contentTypeService) {
                    return $contentTypeService->loadContentType($uriParameters['type']);
                },
                'typeByIdentifier' => function ($uriParameters) use ($contentTypeService) {
                    return $contentTypeService->loadContentTypeByIdentifier($uriParameters['type']);
                },
                'typeByRemoteId' => function ($uriParameters) use ($contentTypeService) {
                    return $contentTypeService->loadContentTypeByRemoteId($uriParameters['type']);
                },

                'urlAlias' => function ($uriParameters) use ($urlAliasService) {
                    return $urlAliasService->load($uriParameters['urlAlias']);
                },
                'urlAliasByUrl' => function ($uriParameters) use ($urlAliasService) {
                    return $urlAliasService->lookup($uriParameters['urlAlias']);
                },

                'section' => function ($uriParameters) use ($sectionService) {
                    return $sectionService->loadSection($uriParameters['section']);
                },
                'sectionByIdentifier' => function ($uriParameters) use ($sectionService) {
                    return $sectionService->loadSectionByIdentifier($uriParameters['section']);
                },

                'user' => function ($uriParameters) use ($userService) {
                    return $userService->loadUser($uriParameters['user']);
                },
                // TODO: User by Remote ID
                'userByLogin' => function ($uriParameters) use ($userService) {
                    return $userService->loadUserByLogin($uriParameters['user']);
                },
            ]
        );
    }
}
