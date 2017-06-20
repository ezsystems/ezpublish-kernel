<?php

namespace eZ\Publish\Core\REST\Server;

use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\API\Repository\ContentTypeService;

class ResourceResolver
{
    /**
     * @var RequestParser
     */
    private $requestParser;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param RequestParser $requestParser
     * @param ContentTypeService $contentTypeService
     */
    public function __construct(RequestParser $requestParser, ContentTypeService $contentTypeService)
    {
        $this->requestParser = $requestParser;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param string $uri
     */
    public function resolveContentType($uri)
    {
        $contentTypeVariables = $this->requestParser->parse($uri);
        $uriType = $this->requestParser->parseType($uri);

        switch ($uriType) {
            case 'typeByIdentifier':
                return $this->contentTypeService->loadContentTypeByIdentifier(
                    $contentTypeVariables['type']
                );

            case 'typeByRemoteId':
                return $this->contentTypeService->loadContentTypeByRemoteId(
                    $contentTypeVariables['type']
                );

            case 'type':
                return $this->contentTypeService->loadContentType(
                    $contentTypeVariables['type']
                );
        }

        throw new Exceptions\InvalidArgumentException("Could not retrieve ContenType for '$uri'.");
    }
}
