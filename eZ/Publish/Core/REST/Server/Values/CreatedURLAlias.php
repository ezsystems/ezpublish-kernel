<?php
/**
 * File containing the CreatedURLAlias class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;
use \eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created URLAlias.
 */
class CreatedURLAlias extends ValueObject
{
    /**
     * The created URL alias
     *
     * @var \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public $urlAlias;
}
