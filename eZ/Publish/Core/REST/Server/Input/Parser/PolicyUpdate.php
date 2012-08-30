<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\RoleService;

/**
 * Base class for input parser
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
     * Construct from role service
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
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
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $policyUpdate = $this->roleService->newPolicyUpdateStruct();

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
                    $parsingDispatcher->parse(
                        $limitationData, $limitationData['_media-type']
                    )
                );
            }
        }

        return $policyUpdate;
    }
}

