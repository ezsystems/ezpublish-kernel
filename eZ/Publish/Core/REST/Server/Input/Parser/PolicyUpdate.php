<?php
/**
 * File containing the PolicyUpdate parser class
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
 * Parser for PolicyUpdate
 */
class PolicyUpdate extends Base
{
    /**
     * Role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Parser tools
     *
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
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $policyUpdate = $this->roleService->newPolicyUpdateStruct();

        // @todo XSD says that limitations field is mandatory, but
        // it needs to be possible to remove limitations from policy
        if ( array_key_exists( 'limitations', $data ) )
        {
            if ( !is_array( $data['limitations'] ) )
            {
                throw new Exceptions\Parser( "Invalid format for 'limitations' in PolicyUpdate." );
            }

            if ( !isset( $data['limitations']['limitation'] ) || !is_array( $data['limitations']['limitation'] ) )
            {
                throw new Exceptions\Parser( "Invalid format for 'limitations' in PolicyUpdate." );
            }

            foreach ( $data['limitations']['limitation'] as $limitationData )
            {
                $policyUpdate->addLimitation(
                    $this->parserTools->parseLimitation( $limitationData )
                );
            }
        }

        return $policyUpdate;
    }
}
