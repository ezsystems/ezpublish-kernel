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
 *
 */
interface EndpointProvider
{
    /**
     *
     *
     * @return string
     */
    public function getEntryPoint();

    /**
     *
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getIndexingTarget( $languageCode );

    /**
     *
     *
     * @param array $languageSettings
     *
     * @return string[]
     */
    public function getSearchTargets( array $languageSettings );

    /**
     *
     *
     * @return string[]
     */
    public function getAllEndpoints();
}
