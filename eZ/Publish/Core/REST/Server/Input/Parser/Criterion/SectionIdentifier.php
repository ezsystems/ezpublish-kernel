<?php

/**
 * File containing the SectionIdentifier Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId as SectionIdCriterion;

/**
 * Parser for SectionIdentifier Criterion.
 */
class SectionIdentifier extends BaseParser
{
    /**
     * Section service.
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * Parses input structure to a SectionIdentifier Criterion object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRestCommon\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('SectionIdentifierCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <SectionIdentifierCriterion> format');
        }
        $section = $this->sectionService->loadSectionByIdentifier($data['SectionIdentifierCriterion']);

        return new SectionIdCriterion($section->id);
    }
}
