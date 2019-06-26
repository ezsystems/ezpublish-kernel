<?php

/**
 * File containing the ContentInfo parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\API\Repository\ContentTypeService;

/**
 * Parser for ContentInfo.
 */
class ContentInfo extends BaseParser
{
    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
    protected $parserTools;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /**
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ParserTools $parserTools, ContentTypeService $contentTypeService)
    {
        $this->parserTools = $parserTools;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     *
     * @todo Error handling
     * @todo What about missing properties? Set them here, using the service to
     *       load? Or better set them in the service, since loading is really
     *       unsuitable here?
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contentTypeId = $this->parserTools->parseObjectElement($data['ContentType'], $parsingDispatcher);
        $ownerId = $this->parserTools->parseObjectElement($data['Owner'], $parsingDispatcher);
        $mainLocationId = $this->parserTools->parseObjectElement($data['MainLocation'], $parsingDispatcher);
        $sectionId = $this->parserTools->parseObjectElement($data['Section'], $parsingDispatcher);

        $locationListReference = $this->parserTools->parseObjectElement($data['Locations'], $parsingDispatcher);
        $versionListReference = $this->parserTools->parseObjectElement($data['Versions'], $parsingDispatcher);
        $currentVersionReference = $this->parserTools->parseObjectElement($data['CurrentVersion'], $parsingDispatcher);

        if (isset($data['CurrentVersion']['Version'])) {
            $this->parserTools->parseObjectElement($data['CurrentVersion']['Version'], $parsingDispatcher);
        }

        return new Values\RestContentInfo(
            array(
                'id' => $data['_href'],
                'name' => $data['Name'],
                'contentTypeId' => $contentTypeId,
                'ownerId' => $ownerId,
                'modificationDate' => new \DateTime($data['lastModificationDate']),

                'publishedDate' => ($publishedDate = (!empty($data['publishedDate'])
                    ? new \DateTime($data['publishedDate'])
                    : null)),

                'published' => ($publishedDate !== null),
                'alwaysAvailable' => (strtolower($data['alwaysAvailable']) === 'true'),
                'remoteId' => $data['_remoteId'],
                'mainLanguageCode' => $data['mainLanguageCode'],
                'mainLocationId' => $mainLocationId,
                'sectionId' => $sectionId,

                'versionListReference' => $versionListReference,
                'locationListReference' => $locationListReference,
                'currentVersionReference' => $currentVersionReference,
            )
        );
    }
}
