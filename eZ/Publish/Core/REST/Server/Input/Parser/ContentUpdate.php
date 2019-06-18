<?php

/**
 * File containing the ContentUpdate parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct;
use DateTime;
use Exception;

/**
 * Parser for ContentUpdate.
 */
class ContentUpdate extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @todo use url handler instead of hardcoded URL matching
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestContentMetadataUpdateStruct
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser if $data is invalid
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $parsedData = [];

        if (array_key_exists('Section', $data) && is_array($data['Section']) && isset($data['Section']['_href'])) {
            try {
                $parsedData['sectionId'] = $this->requestParser->parseHref($data['Section']['_href'], 'sectionId');
            } catch (Exceptions\InvalidArgumentException $e) {
                throw new Exceptions\Parser('Invalid format for <Section> reference in <ContentUpdate>.');
            }
        }

        if (array_key_exists('Owner', $data) && is_array($data['Owner']) && isset($data['Owner']['_href'])) {
            try {
                $parsedData['ownerId'] = $this->requestParser->parseHref($data['Owner']['_href'], 'userId');
            } catch (Exceptions\InvalidArgumentException $e) {
                throw new Exceptions\Parser('Invalid format for <Owner> reference in <ContentUpdate>.');
            }
        }

        if (array_key_exists('mainLanguageCode', $data)) {
            $parsedData['mainLanguageCode'] = $data['mainLanguageCode'];
        }

        if (array_key_exists('MainLocation', $data)) {
            try {
                $mainLocationIdParts = explode('/', $this->requestParser->parseHref($data['MainLocation']['_href'], 'locationPath'));
                $parsedData['mainLocationId'] = array_pop($mainLocationIdParts);
            } catch (Exceptions\InvalidArgumentException $e) {
                throw new Exceptions\Parser('Invalid format for <MainLocation> reference in <ContentUpdate>.');
            }
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            if ($data['alwaysAvailable'] === 'true') {
                $parsedData['alwaysAvailable'] = true;
            } elseif ($data['alwaysAvailable'] === 'false') {
                $parsedData['alwaysAvailable'] = false;
            } else {
                throw new Exceptions\Parser('Invalid format for <alwaysAvailable> in <ContentUpdate>.');
            }
        }

        // remoteId
        if (array_key_exists('remoteId', $data)) {
            $parsedData['remoteId'] = $data['remoteId'];
        }

        // modificationDate
        if (array_key_exists('modificationDate', $data)) {
            try {
                $parsedData['modificationDate'] = new DateTime($data['modificationDate']);
            } catch (Exception $e) {
                throw new Exceptions\Parser('Invalid format for <modificationDate> in <ContentUpdate>', 0, $e);
            }
        }

        // publishDate
        if (array_key_exists('publishDate', $data)) {
            try {
                $parsedData['publishedDate'] = new DateTime($data['publishDate']);
            } catch (Exception $e) {
                throw new Exceptions\Parser('Invalid format for <publishDate> in <ContentUpdate>', 0, $e);
            }
        }

        return new RestContentMetadataUpdateStruct($parsedData);
    }
}
