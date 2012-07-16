<?php
/**
 * File containing the Policy parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Input\Parser;

use eZ\Publish\API\REST\Common\Input\Parser;
use eZ\Publish\API\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\API\REST\Client;

/**
 * Parser for Policy
 */
class Policy extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     * @return ValueObject
     * @todo Error handling
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $limitations = array();

        if ( array_key_exists( 'limitations', $data ) )
        {
            foreach ( $data['limitations']['limitation'] as $limitation )
            {
                $limitationObject = $this->getLimitationByIdentifier( $limitation['_identifier'] );

                $limitationValues = array();
                foreach ( $limitation['values']['ref'] as $limitationValue )
                {
                    $limitationValues[] = $limitationValue['_href'];
                }

                $limitationObject->limitationValues = $limitationValues;
                $limitations[] = $limitationObject;
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
