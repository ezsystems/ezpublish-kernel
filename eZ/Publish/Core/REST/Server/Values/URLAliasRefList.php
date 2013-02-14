<?php
/**
 * File containing the URLAliasRefList class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * URLAlias ref list view model
 */
class URLAliasRefList extends RestValue
{
    /**
     * URL aliases
     *
     * @var \eZ\Publish\API\Repository\Values\Content\URLAlias[]
     */
    public $urlAliases;

    /**
     * Path that was used to fetch the list of URL aliases
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias[] $urlAliases
     * @param string $path
     */
    public function __construct( array $urlAliases, $path )
    {
        $this->urlAliases = $urlAliases;
        $this->path = $path;
    }
}
