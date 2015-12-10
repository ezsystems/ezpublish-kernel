<?php
/**
 * File containing the WordIndexer Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content\WordIndexer;

use eZ\Publish\SPI\Persistence\Content;

/**
 * The WordIndexer Gateway abstracts indexing of content full text data.
 */
abstract class Gateway
{
    /**
     * Add a version of a Content to index.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    abstract public function index(Content $content);

    /**
     * Remove whole content or a specific version from index.
     *
     * @param mixed $contentId
     * @param mixed|null $versionId
     */
    abstract public function remove($contentId, $versionId = null);
}
