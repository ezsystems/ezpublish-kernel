<?php

/**
 * File containing the ContentObjectStates parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Common\Values\RestObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;

/**
 * Parser for ContentObjectStates.
 */
class ContentObjectStates extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestObjectState[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        // @todo XSD says that no ObjectState elements is valid,
        // but we should at least have one if setting new states to content?
        if (!array_key_exists('ObjectState', $data) || !is_array($data['ObjectState']) || empty($data['ObjectState'])) {
            throw new Exceptions\Parser("Missing or invalid 'ObjectState' elements for ContentObjectStates.");
        }

        $states = [];
        foreach ($data['ObjectState'] as $rawStateData) {
            if (!array_key_exists('_href', $rawStateData)) {
                throw new Exceptions\Parser("Missing '_href' attribute for ObjectState.");
            }

            $states[] = new RestObjectState(
                new ObjectState(
                    [
                        'id' => $this->requestParser->parseHref($rawStateData['_href'], 'objectStateId'),
                    ]
                ),
                $this->requestParser->parseHref($rawStateData['_href'], 'objectStateGroupId')
            );
        }

        return $states;
    }
}
