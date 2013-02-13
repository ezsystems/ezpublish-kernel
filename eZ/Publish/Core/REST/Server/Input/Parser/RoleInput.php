<?php
/**
 * File containing the RoleInput parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\RoleService;

/**
 * Parser for RoleInput
 */
class RoleInput extends Base
{
    /**
     * Role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( UrlHandler $urlHandler, RoleService $roleService, ParserTools $parserTools )
    {
        parent::__construct( $urlHandler );
        $this->roleService = $roleService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        // Since RoleInput is used both for creating and updating role and identifier is not
        // required when updating role, we need to rely on PAPI to throw the exception on missing
        // identifier when creating a role
        // @todo Bring in line with XSD which says that identifier is required always

        $roleIdentifier = null;
        if ( array_key_exists( 'identifier', $data ) )
        {
            $roleIdentifier = $data['identifier'];
        }

        $roleCreateStruct = $this->roleService->newRoleCreateStruct( $roleIdentifier );

        return $roleCreateStruct;
    }
}
