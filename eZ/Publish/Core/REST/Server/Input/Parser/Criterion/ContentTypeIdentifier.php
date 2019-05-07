<?php

/**
 * File containing the ContentTypeIdentifier Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;

/**
 * Parser for ViewInput.
 */
class ContentTypeIdentifier extends BaseParser
{
    /**
     * Content type service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRestCommon\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('ContentTypeIdentifierCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <ContentTypeIdCriterion> format');
        }
        if (!is_array($data['ContentTypeIdentifierCriterion'])) {
            $data['ContentTypeIdentifierCriterion'] = [$data['ContentTypeIdentifierCriterion']];
        }

        return new ContentTypeIdCriterion(
            array_map(
                function ($contentTypeIdentifier) {
                    return $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier)->id;
                },
                $data['ContentTypeIdentifierCriterion']
            )
        );
    }
}
