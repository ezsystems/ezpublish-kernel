<?php
/**
 * File containing the ContentTypeGroupRefList class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * ContentTypeGroup list view model
 */
class ContentTypeGroupRefList extends RestValue
{
    /**
     * Content type
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    /**
     * Content type groups of the content type
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public $contentTypeGroups;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups
     */
    public function __construct( ContentType $contentType, array $contentTypeGroups )
    {
        $this->contentType = $contentType;
        $this->contentTypeGroups = $contentTypeGroups;
    }
}
