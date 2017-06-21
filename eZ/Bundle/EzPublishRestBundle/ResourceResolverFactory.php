<?php

namespace eZ\Bundle\EzPublishRestBundle;

use eZ\Publish\Core\REST\Common\RequestParser;

use eZ\Publish\API\Repository\Repository;

class ResourceResolverFactory
{
    /**
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    private $requestParser;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(
        RequestParser $requestParser,
        Repository $repository
    ) {
        $this->requestParser = $requestParser;
        $this->repository = $repository;
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\ResourceResolver
     */
    public function createResolver()
    {
        $contentTypeService = $this->repository->getContentTypeService();
        $urlAliasService = $this->repository->getUrlAliasService();
        $sectionService = $this->repository->getSectionService();
        $userService = $this->repository->getUserService();

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
