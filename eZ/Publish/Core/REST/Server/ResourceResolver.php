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
     * @var callable[string]
     */
    protected $loaderMap;

    /**
     * @param RequestParser $requestParser
     * @param callable[string] $loaderMap
     */
    public function __construct(RequestParser $requestParser, array $loaderMap)
    {
        $this->requestParser = $requestParser;

        foreach ($loaderMap as $uriType => $loaderFunction) {
            $this->addLoader($uriType, $loaderFunction);
        }
    }

    private function addLoader($uriType, callable $loaderFunction)
    {
        $this->loaderMap[$uriType] = $loaderFunction;
    }

    public function resolve($uri)
    {
        $uriType = $this->requestParser->parseType($uri);

        if (!isset($this->loaderMap[$uriType])) {
            throw new Exceptions\InvalidArgumentException("No loader defined for type '$uriType'.");
        }
        $loaderFunction = $this->loaderMap[$uriType];

        $loaderParameters = $this->requestParser->parse($uri);

        return $loaderFunction($loaderParameters);
    }
}
