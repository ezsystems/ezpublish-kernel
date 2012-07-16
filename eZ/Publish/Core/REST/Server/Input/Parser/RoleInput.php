<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Input\Parser;
use eZ\Publish\API\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\REST\Common\UrlHandler;
use eZ\Publish\API\REST\Common\Exceptions;
use eZ\Publish\API\Repository\RoleService;

use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;

/**
 * Base class for input parser
 */
class RoleInput extends Base
{
    /**
     * Role service
     *
     * @var RoleService
     */
    protected $roleService;

    /**
     * Construct from role service
     *
     * @param \eZ\Publish\API\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     */
    public function __construct( UrlHandler $urlHandler, RoleService $roleService )
    {
        parent::__construct( $urlHandler );
        $this->roleService = $roleService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\API\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'identifier', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'identifier' attribute for RoleInput." );
        }

        return $this->roleService->newRoleCreateStruct( $data['identifier'] );
    }
}

