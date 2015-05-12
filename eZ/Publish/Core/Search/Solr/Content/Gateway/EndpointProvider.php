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
 * Endpoint provider provides Solr backend endpoints
 */
interface EndpointProvider
{
    /**
     * Returns the endpoint used for distributed search
     *
     * @return string
     */
    public function getEntryPoint();

    /**
     * Returns endpoint that indexes Content translations in the given $languageCode
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getIndexingTarget( $languageCode );

    /**
     * Returns an array of endpoints for the given $languageSettings
     *
     * @param array $languageSettings
     *
     * @return string[]
     */
    public function getSearchTargets( array $languageSettings );

    /**
     * Returns all endpoints
     *
     * @return string[]
     */
    public function getAllEndpoints();
}
