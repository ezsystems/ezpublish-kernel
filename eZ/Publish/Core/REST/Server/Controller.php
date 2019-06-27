<?php

/**
 * File containing the REST Server Controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server;

use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\RouterInterface;
use eZ\Publish\Core\REST\Common\Input\Dispatcher as InputDispatcher;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\REST\Common\RequestParser;

abstract class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var \eZ\Publish\Core\REST\Common\Input\Dispatcher */
    protected $inputDispatcher;

    /** @var \Symfony\Component\Routing\RouterInterface */
    protected $router;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser */
    protected $requestParser;

    /**
     * Repository.
     *
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function setInputDispatcher(InputDispatcher $inputDispatcher)
    {
        $this->inputDispatcher = $inputDispatcher;
    }

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function setRequestParser(RequestParser $requestParser)
    {
        $this->requestParser = $requestParser;
    }

    /**
     * Extracts the requested media type from $request.
     *
     * @todo refactor, maybe to a REST Request with an accepts('content-type') method
     *
     * @return string
     */
    protected function getMediaType(Request $request)
    {
        foreach ($request->getAcceptableContentTypes() as $mimeType) {
            if (preg_match('(^([a-z0-9-/.]+)\+.*$)', strtolower($mimeType), $matches)) {
                return $matches[1];
            }
        }

        return 'unknown/unknown';
    }
}
