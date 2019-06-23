<?php

/**
 * File containing the UrlWildcard Mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * UrlWildcard Mapper.
 */
class Mapper
{
    /**
     * Creates a UrlWildcard object from given parameters.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param bool $forward
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function createUrlWildcard($sourceUrl, $destinationUrl, $forward)
    {
        $urlWildcard = new UrlWildcard();

        $urlWildcard->destinationUrl = $this->cleanUrl($destinationUrl);
        $urlWildcard->sourceUrl = $this->cleanUrl($sourceUrl);
        $urlWildcard->forward = $forward;

        return $urlWildcard;
    }

    /**
     * Extracts UrlWildcard object from given database $row.
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function extractUrlWildcardFromRow(array $row)
    {
        $urlWildcard = new UrlWildcard();

        $urlWildcard->id = (int)$row['id'];
        $urlWildcard->destinationUrl = $this->cleanUrl($row['destination_url']);
        $urlWildcard->sourceUrl = $this->cleanUrl($row['source_url']);
        $urlWildcard->forward = (int)$row['type'] === 1;

        return $urlWildcard;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function cleanUrl($url)
    {
        // if given $url is an absolute URL, then it's not necessary to prepend it with slash
        if (null !== parse_url($url, PHP_URL_SCHEME)) {
            return trim($url);
        }

        return '/' . trim($url, '/ ');
    }

    /**
     * Extracts UrlWildcard objects from database $rows.
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function extractUrlWildcardsFromRows(array $rows)
    {
        $urlWildcards = [];

        foreach ($rows as $row) {
            $urlWildcards[] = $this->extractUrlWildcardFromRow($row);
        }

        return $urlWildcards;
    }
}
