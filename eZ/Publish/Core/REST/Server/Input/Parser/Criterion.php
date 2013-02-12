<?php
/**
 * File containing the ContentIdCriterion parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\Base;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion as APICriterion;

/**
 * Parser for ViewInput
 */
abstract class Criterion extends Base
{
    /**
     * @var array
     */
    protected static $criterionIdMap = array(
        'AND' => 'LogicalAnd',
        'OR'  => 'LogicalOr',
        'NOT' => 'LogicalNot',
    );

    /**
     * Dispatches parsing of a criterion name + data to its own parser
     * @param string $criterionName
     * @param mixed $criterionData
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function dispatchCriterion( $criterionName, $criterionData, ParsingDispatcher $parsingDispatcher )
    {
        $mediaType = $this->getCriterionMediaType( $criterionName );
        try
        {
            return $parsingDispatcher->parse( array( $criterionName => $criterionData ), $mediaType );
        }
        catch ( Exceptions\Parser $e )
        {
            throw new Exceptions\Parser( "Invalid Criterion id <$criterionName> in <AND>", 0, $e );
        }
    }

    protected function getCriterionMediaType( $criterionName )
    {
        $criterionName = str_replace( 'Criterion', '', $criterionName );
        if ( isset( self::$criterionIdMap[$criterionName] ) )
        {
            $criterionName = self::$criterionIdMap[$criterionName];
        }
        return 'application/vnd.ez.api.internal.criterion.' . $criterionName;
    }
}
