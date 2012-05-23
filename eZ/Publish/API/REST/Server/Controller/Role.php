<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Controller;
use eZ\Publish\API\REST\Common\UrlHandler;
use eZ\Publish\API\REST\Common\Message;
use eZ\Publish\API\REST\Common\Input;
use eZ\Publish\API\REST\Server\Values;

use \eZ\Publish\API\Repository\RoleService;
use \eZ\Publish\API\Repository\Values\Content\RoleCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\RoleUpdateStruct;

use Qafoo\RMF;

/**
 * Role controller
 */
class Role
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\API\REST\Server\InputDispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\API\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Construct controller
     *
     * @param Input\Dispatcher $inputDispatcher
     * @param UrlHandler $urlHandler
     * @param RoleService $roleService
     * @return void
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, RoleService $roleService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->roleService     = $roleService;
    }

    /**
     * Create new section
     *
     * @param RMF\Request $request
     * @return mixed
     */
    public function createRole( RMF\Request $request )
    {
        return new Values\CreatedRole( array(
            'role' => $this->roleService->createRole(
                $this->inputDispatcher->parse( new Message(
                    array( 'Content-Type' => $request->contentType ),
                    $request->body
                ) )
            ) )
        );
    }
}
