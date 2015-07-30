<?php

/**
 * File containing the authenticating dispatcher class.
 *
 * ATTENTION: This is a test setup for the REST server. DO NOT USE IT IN
 * PRODUCTION!
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server;

use Qafoo\RMF;
use Exception;

/**
 * MVC dispatcher with integrated authentication.
 */
class AuthenticatingDispatcher extends RMF\Dispatcher\Simple
{
    /**
     * Authenticator.
     *
     * @var \eZ\Publish\Core\REST\Server\Authenticator
     */
    protected $authenticator;

    /**
     * Creates a new authenticating dispatcher.
     *
     * @param RMF\Router $router
     * @param RMF\View $view
     * @param \eZ\Publish\Core\REST\Server\Authenticator $authenticator
     */
    public function __construct(RMF\Router $router, RMF\View $view, Authenticator $authenticator)
    {
        parent::__construct($router, $view);
        $this->authenticator = $authenticator;
    }

    /**
     * Performs authentication and dispatches the request.
     *
     * @param RMF\Request $request
     */
    public function dispatch(RMF\Request $request)
    {
        try {
            $this->authenticator->authenticate($request);
        } catch (Exception $e) {
            $this->view->display($request, $e);
        }
        parent::dispatch($request);
    }
}
