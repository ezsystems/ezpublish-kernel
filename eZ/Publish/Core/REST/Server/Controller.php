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
use eZ\Publish\Core\REST\Common\UrlHandler\eZPublish as UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher as InputDispatcher;
use eZ\Publish\Core\REST\Server\Request as HttpRequest;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

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
     * @var \eZ\Publish\Core\REST\Common\UrlHandler\eZPublish
     */
    protected $urlHandler;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

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

    public function setUrlHandler( UrlHandler $urlHandler )
    {
        $this->urlHandler = $urlHandler;
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
}
