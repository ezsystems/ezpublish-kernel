<?php
/**
 * File containing the LogicalNot Criterion parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Server\Input\Parser\Criterion as CriterionParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot as LogicalNotCriterion;

/**
 * Parser for LogicalNot Criterion
 */
class LogicalNot extends CriterionParser
{
    /**
     * Parses input structure to a Criterion object
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( "NOT", $data ) && !is_array( $data["NOT"] ) )
        {
            throw new Exceptions\Parser( "Invalid <NOT> format" );
        }

        if ( count( $data['NOT'] ) > 1 )
        {
            throw new Exceptions\Parser( "NOT element can only contain one subitem" );
        }
        list( $criterionName, $criterionData ) = each( $data['NOT'] );
        $criteria = $this->dispatchCriterion( $criterionName, $criterionData, $parsingDispatcher );

        return new LogicalNotCriterion( $criteria );
    }
}
