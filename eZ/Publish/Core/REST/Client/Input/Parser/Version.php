<?php

/**
 * File containing the VersionInfo parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\REST\Server\Values\Version as VersionValue;

/**
 * Parser for VersionInfo.
 */
class Version extends BaseParser
{
    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
    protected $parserTools;

    /**
     * Content Service.
     *
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /**
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ParserTools $parserTools, ContentService $contentService, ContentTypeService $contentTypeService)
    {
        $this->parserTools = $parserTools;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\Version
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contentId = $this->requestParser->parseHref($data['VersionInfo']['Content']['_href'], 'contentId');

        $content = $this->contentService->loadContent($contentId, null, $data['VersionInfo']['versionNo']);
        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        $relations = $this->contentService->loadRelations($content->versionInfo);

        return new VersionValue($content, $contentType, $relations);
    }
}
