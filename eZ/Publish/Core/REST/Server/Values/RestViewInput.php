<?php
/**
 * File containing the RestViewInput class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestContentCreateStruct view model
 */
class RestViewInput extends RestValue
{
    /**
     * The search query
     * @var \eZ\Publish\API\Repository\Values\Content\Query
     */
    public $query;

    /**
     * View identifier
     * @var string
     */
    public $identifier;
}
