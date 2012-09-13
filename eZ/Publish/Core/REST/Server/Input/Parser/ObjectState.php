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
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Common\Values\ObjectState as CommonObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState as CoreObjectState;

/**
 * Base class for input parser
 */
class ObjectState extends Base
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\Core\REST\Common\Values\ObjectState
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( '_href', $data ) )
        {
            throw new Exceptions\Parser( "Missing '_href' attribute for ObjectState." );
        }

        $values = $this->urlHandler->parse( 'objectstate', $data['_href'] );

        return new CommonObjectState(
            new CoreObjectState(
                array(
                    'id' => $values['objectstate']
                )
            ),
            $values['objectstategroup']
        );
    }
}
