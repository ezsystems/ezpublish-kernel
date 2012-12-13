<?php
/**
 * File containing the Version class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Version view model
 */
class Version extends RestValue
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    public $content;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public $relations;

    /**
     * Path used to load this content
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     * @param string $path
     */
    public function __construct( Content $content, array $relations, $path = null )
    {
        $this->content = $content;
        $this->relations = $relations;
        $this->path = $path;
    }
}
