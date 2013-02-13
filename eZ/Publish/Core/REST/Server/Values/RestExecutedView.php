<?php
/**
 * File containing the RestViewInput class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * RestContentCreateStruct view model
 */
class RestExecutedView extends ValueObject
{
    /**
     * The search results
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public $searchResults;

    /**
     * The view identifier
     *
     * @var mixed
     */
    public $identifier;
}
