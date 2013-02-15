<?php
/**
 * File containing the RoleAssignment parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitation;

use eZ\Publish\Core\REST\Client;

/**
 * Parser for RoleAssignment
 */
class RoleAssignment extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment
     * @todo Error handling
     * @todo Use dependency injection system for Role Limitation lookup
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $roleLimitation = null;
        if ( array_key_exists( 'limitation', $data ) )
        {
            $limitation = $parsingDispatcher->parse( $data['limitation'], $data['limitation']['_media-type'] );
            switch ( $limitation->getIdentifier() )
            {
                case APILimitation::SECTION:
                    $roleLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation();
                    break;

                case APILimitation::SUBTREE:
                    $roleLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation();
                    break;

                default:
                    throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'RoleLimitation', $limitation->getIdentifier() );
            }

            $roleLimitation->limitationValues = $limitation->limitationValues;
        }

        return new Client\Values\User\RoleAssignment(
            array(
                'role' => $parsingDispatcher->parse( $data['Role'], $data['Role']['_media-type'] ),
                'limitation' => $roleLimitation
            )
        );
    }
}
