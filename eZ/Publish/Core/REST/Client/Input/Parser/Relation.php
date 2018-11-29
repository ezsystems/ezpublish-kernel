<?php

/**
 * File containing the Relation parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\API\Repository\ContentService;

/**
 * Parser for Relation.
 */
class Relation extends BaseParser
{
    /**
     * Content Service.
     *
     * @var \eZ\Publish\Core\REST\Input\ContentService
     */
    protected $contentService;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Relation\Version
     *
     * @todo Error handling
     * @todo Should the related ContentInfo structs really be loaded here or do
     *       we need lazy loading for this?
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        return new Values\Content\Relation(
            array(
                'id' => $data['_href'],
                'sourceContentInfo' => $this->contentService->loadContentInfo(
                    $data['SourceContent']['_href']
                ),
                'destinationContentInfo' => $this->contentService->loadContentInfo(
                    $data['DestinationContent']['_href']
                ),
                'type' => $this->convertRelationType($data['RelationType']),
                // @todo: Handle SourceFieldDefinitionIdentifier
            )
        );
    }

    /**
     * Converts the string representation of the relation type to its constant.
     *
     * @param string $stringType
     *
     * @return int
     */
    protected function convertRelationType($stringType)
    {
        $stringTypeList = explode(',', strtoupper($stringType));
        $relationType = 0;

        foreach ($stringTypeList as $stringTypeValue) {
            switch ($stringTypeValue) {
                case 'COMMON':
                    $relationType |= Values\Content\Relation::COMMON;
                    break;
                case 'EMBED':
                    $relationType |= Values\Content\Relation::EMBED;
                    break;
                case 'LINK':
                    $relationType |= Values\Content\Relation::LINK;
                    break;
                case 'FIELD':
                    $relationType |= Values\Content\Relation::FIELD;
                    break;
                case 'ASSET':
                    $relationType |= Values\Content\Relation::ASSET;
                    break;
                default:
                    throw new \RuntimeException(
                        sprintf('Unknown Relation type: "%s"', $stringTypeValue)
                    );
            }
        }

        return $relationType;
    }
}
