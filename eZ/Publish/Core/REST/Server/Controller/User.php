<?php
/**
 * File containing the User controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\UserService;

use Qafoo\RMF;

/**
 * User controller
 */
class User
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * User service
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\UserService $userService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, UserService $userService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->userService  = $userService;
    }

    /**
     * Redirects to the root user group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\PermanentRedirect
     */
    public function loadRootUserGroup( RMF\Request $request )
    {
        return new Values\PermanentRedirect(
            $this->urlHandler->generate( 'group', array( 'group' => '/1/5' ) ),
            'UserGroup'
        );
    }
}
