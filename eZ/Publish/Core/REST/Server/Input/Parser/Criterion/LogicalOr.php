<?php
/**
 * File containing the LogicalOr Criterion parser class
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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr as LogicalOrCriterion;

/**
 * Parser for LogicalOr Criterion
 */
class LogicalOr extends CriterionParser
{
    /**
     * Parses input structure to a LogicalAnd Criterion object
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( "OR", $data ) && !is_array( $data["OR"] ) )
        {
            throw new Exceptions\Parser( "Invalid <OR> format" );
        }

        $criteria = array();
        foreach ( $data["OR"] as $criterionName => $criterionData )
        {
            $criteria[] = $this->dispatchCriterion( $criterionName, $criterionData, $parsingDispatcher );
        }

        return new LogicalOrCriterion( $criteria );
    }
}
