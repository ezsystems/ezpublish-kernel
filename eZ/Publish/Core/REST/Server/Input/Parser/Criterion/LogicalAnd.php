<?php
/**
 * File containing the LogicalAnd Criterion parser class
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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;

/**
 * Parser for LogicalAnd Criterion
 */
class LogicalAnd extends CriterionParser
{
    /**
     * Parses input structure to a LogicalAnd Criterion object
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( "AND", $data ) && !is_array( $data['AND'] ) )
        {
            throw new Exceptions\Parser( "Invalid <AND> format" );
        }

        $criteria = array();
        foreach ( $data["AND"] as $criterionName => $criterionData )
        {
            $criteria[] = $this->dispatchCriterion( $criterionName, $criterionData, $parsingDispatcher );
        }

        return new LogicalAndCriterion( $criteria );
    }
}
