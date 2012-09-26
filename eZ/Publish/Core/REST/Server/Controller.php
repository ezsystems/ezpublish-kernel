<?php
namespace eZ\Publish\Core\REST\Server;

use eZ\Publish\Core\REST\Common\UrlHandler\eZPublish as UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher as InputDispatcher;
use \Symfony\Component\HttpFoundation\Request as HttpRequest;

abstract class Controller
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $urlHandler;

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
        // @todo Decorate $request as an RMF\Request like object
        $this->request = $request;
    }
}
