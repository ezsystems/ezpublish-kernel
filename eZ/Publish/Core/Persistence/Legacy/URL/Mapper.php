<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\URL;

use eZ\Publish\SPI\Persistence\URL\URL;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;

/**
 * URL Mapper.
 */
class Mapper
{
    /**
     * Creates a URL from the given update $struct.
     *
     * @param \eZ\Publish\SPI\Persistence\URL\URLUpdateStruct $struct
     * @return \eZ\Publish\SPI\Persistence\URL\URL
     */
    public function createURLFromUpdateStruct(URLUpdateStruct $struct)
    {
        $url = new URL();
        $url->url = $struct->url;
        $url->originalUrlMd5 = md5($struct->url);
        $url->isValid = $struct->isValid;
        $url->lastChecked = $struct->lastChecked;
        $url->modified = time();

        return $url;
    }

    /**
     * Extracts URL objects from $rows.
     *
     * @param array $rows
     * @return \eZ\Publish\SPI\Persistence\URL\URL[]
     */
    public function extractURLsFromRows(array $rows)
    {
        $urls = [];

        foreach ($rows as $row) {
            $url = new URL();
            $url->id = (int)$row['id'];
            $url->url = $row['url'];
            $url->originalUrlMd5 = $row['original_url_md5'];
            $url->isValid = (bool)$row['is_valid'];
            $url->lastChecked = (int)$row['last_checked'];
            $url->created = (int)$row['created'];
            $url->modified = (int)$row['modified'];

            $urls[] = $url;
        }

        return $urls;
    }
}
