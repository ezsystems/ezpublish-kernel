<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Mapper maps Content and Location objects to a Document object, representing a
 * document in Solr index storage.
 *
 * Note that custom implementations might need to be accompanied by custom schema.
 */
interface DocumentMapper
{
    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapContent( Content $content );

    /**
     * Maps given Location to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapLocation( Location $location );
}
