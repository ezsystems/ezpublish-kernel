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
use eZ\Publish\API\Repository\LocationService;

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
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, UserService $userService, LocationService $locationService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler = $urlHandler;
        $this->userService = $userService;
        $this->locationService = $locationService;
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

    /**
     * Loads a user group for the given path
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    public function loadUserGroup( RMF\Request $request )
    {
        $urlValues = $this->urlHandler->parse( 'group', $request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        return new Values\RestUserGroup(
            $userGroup,
            $userGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58
     *
     * @param string $path
     * @return mixed
     */
    private function extractLocationIdFromPath( $path )
    {
        $pathParts = explode( '/', $path );
        return array_pop( $pathParts );
    }
}
