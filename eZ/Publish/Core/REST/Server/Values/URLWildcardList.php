<?php
/**
 * File containing the URLWildcardList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * URLWildcard list view model
 */
class URLWildcardList extends RestValue
{
    /**
     * URL wildcards
     *
     * @var \eZ\Publish\API\Repository\Values\Content\URLWildcard[]
     */
    public $urlWildcards;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard[] $urlWildcards
     */
    public function __construct( array $urlWildcards )
    {
        $this->urlWildcards = $urlWildcards;
    }
}
