<?php

/**
 * File containing the LanguageCode Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use EzSystems\EzPlatformRest\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode as LanguageCodeCriterion;

/**
 * Parser for LanguageCode Criterion.
 */
class LanguageCode extends BaseParser
{
    /**
     * Parses input structure to a LanguageCode Criterion object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRest\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('LanguageCodeCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <LanguageCodeCriterion> format');
        }

        return new LanguageCodeCriterion(explode(',', $data['LanguageCodeCriterion']));
    }
}
