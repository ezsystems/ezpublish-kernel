<?php

/**
 * File containing the ContentIdCriterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for ViewInput.
 */
abstract class Criterion extends BaseParser
{
    /**
     * @var array
     */
    protected static $criterionIdMap = array(
        'AND' => 'LogicalAnd',
        'OR' => 'LogicalOr',
        'NOT' => 'LogicalNot',
    );

    /**
     * Dispatches parsing of a criterion name + data to its own parser.
     *
     * @param string $criterionName
     * @param mixed $criterionData
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function dispatchCriterion($criterionName, $criterionData, ParsingDispatcher $parsingDispatcher)
    {
        $mediaType = $this->getCriterionMediaType($criterionName);
        try {
            return $parsingDispatcher->parse(array($criterionName => $criterionData), $mediaType);
        } catch (Exceptions\Parser $e) {
            throw new Exceptions\Parser("Invalid Criterion id <$criterionName> in <AND>", 0, $e);
        }
    }

    protected function getCriterionMediaType($criterionName)
    {
        $criterionName = str_replace('Criterion', '', $criterionName);
        if (isset(self::$criterionIdMap[$criterionName])) {
            $criterionName = self::$criterionIdMap[$criterionName];
        }

        return 'application/vnd.ez.api.internal.criterion.' . $criterionName;
    }
}
