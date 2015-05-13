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
     * @todo consider moving
     */
    const DOCUMENT_TYPE_CONTENT = "content";

    /**
     * @todo consider moving
     */
    const DOCUMENT_TYPE_LOCATION = "location";

    /**
     * Returns the endpoint used for distributed search, for the given $documentType
     *
     * @param mixed $documentType
     *
     * @return string
     */
    public function getEntryPoint( $documentType );

    /**
     * Returns endpoint that indexes Content translations in the given $documentType and $languageCode
     *
     * @param mixed $documentType
     * @param string $languageCode
     *
     * @return string
     */
    public function getIndexingTarget( $documentType, $languageCode );

    /**
     * Returns an array of endpoints for the given $documentType and $languageSettings
     *
     * @param mixed $documentType
     * @param array $languageSettings
     *
     * @return string[]
     */
    public function getSearchTargets( $documentType, array $languageSettings );

    /**
     * Returns all endpoints for the given $documentType
     *
     * @param mixed $documentType
     *
     * @return string[]
     */
    public function getAllEndpoints( $documentType );
}
