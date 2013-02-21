<?php
/**
 * File containing the Policy parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\Core\REST\Client;

/**
 * Parser for Policy
 */
class Policy extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $limitations = array();

        if ( array_key_exists( 'limitations', $data ) )
        {
            foreach ( $data['limitations']['limitation'] as $limitation )
            {
                $limitations[] = $parsingDispatcher->parse(
                    $limitation,
                    $limitation['_media-type']
                );
            }
        }

        return new Client\Values\User\Policy(
            array(
                'id' => $data['id'],
                'module' => $data['module'],
                'function' => $data['function'],
                'limitations' => $limitations
            )
        );
    }
}
