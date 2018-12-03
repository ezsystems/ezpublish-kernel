<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Mapper maps Content and Location objects to a Document object, representing a
 * document in Elasticsearch index storage.
 *
 * Note that custom implementations might need to be accompanied by custom mappings.
 *
 * @deprecated
 */
interface MapperInterface
{
    /**
     * Maps given Content by given $contentId to a Document.
     *
     * @param int|string $contentId
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    public function mapContentById($contentId);

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    public function mapContent(Content $content);

    /**
     * Maps given Location to a Document.
     *
     * Returned Document represents a "parent" Location document searchable with Location Search.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    public function mapLocation(Location $location);
}
