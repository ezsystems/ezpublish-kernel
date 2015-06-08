<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

use eZ\Publish\SPI\Search\FieldType;

/**
 * Endpoint resolver resolves Solr backend endpoints
 */
interface EndpointResolver
{
    /**
     * Returns the Endpoint used as entry point for distributed search
     *
     * @return \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint
     */
    public function getEntryEndpoint();

    /**
     * Returns Endpoint that indexes Content translations in the given $languageCode
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getIndexingTarget( $languageCode );

    /**
     * Returns an array of Endpoints for the given $languageSettings
     *
     * @param array $languageSettings
     *
     * @return string[]
     */
    public function getSearchTargets( array $languageSettings );

    /**
     * Returns all Endpoints
     *
     * @return string[]
     */
    public function getEndpoints();
}
