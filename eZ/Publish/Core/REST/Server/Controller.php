<?php
/**
 * File containing the REST Server Controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server;

use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Routing\RouterInterface;
use eZ\Publish\Core\REST\Common\Input\Dispatcher as InputDispatcher;
use eZ\Publish\Core\REST\Server\Request as HttpRequest;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use eZ\Publish\Core\REST\Common\UrlHandler as RequestParser;

abstract class Controller
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Request
     */
    protected $request;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $requestParser;

    /**
     * Repository
     *
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    public function setInputDispatcher( InputDispatcher $inputDispatcher )
    {
        $this->inputDispatcher = $inputDispatcher;
    }

    public function setRouter( RouterInterface $urlHandler )
    {
        $this->router = $urlHandler;
    }

    public function setRequest( HttpRequest $request )
    {
        $this->request = $request;
    }

    public function setContainer( Container $container )
    {
        $this->container = $container;
    }

    public function setRepository( Repository $repository )
    {
        $this->repository = $repository;
    }

    public function setRequestParser( RequestParser $requestParser )
    {
        $this->requestParser = $requestParser;
    }
}
