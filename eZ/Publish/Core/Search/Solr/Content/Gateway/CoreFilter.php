<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Core filter applies conditions on a query object ensuring matching of correct
 * document across multiple Solr indexes.
 */
abstract class CoreFilter
{
    /**
     * Applies conditions on the $query using given $languageSettings.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageSettings
     */
    abstract public function apply( Query $query, array $languageSettings );
}
