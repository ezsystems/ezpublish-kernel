<?php
/**
 * File containing the RestContent class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * REST Content, as received by /content/objects/<ID>
 *
 * Might have a "Version" (aka Content in the Public API) embedded
 */
class RestContent extends RestValue
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    public $mainLocation;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    public $currentVersion;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public $relations;

    /**
     * Path that was used to load this content
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location|null $mainLocation
     * @param \eZ\Publish\API\Repository\Values\Content\Content|null $currentVersion
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType Can only be null if $currentVersion is
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[]|null $relations Can only be null if $currentVersion is
     * @param string $path
     */
    public function __construct(
        ContentInfo $contentInfo,
        Location $mainLocation = null,
        Content $currentVersion = null,
        ContentType $contentType = null,
        array $relations = null,
        $path = null
    )
    {
        $this->contentInfo = $contentInfo;
        $this->mainLocation = $mainLocation;
        $this->currentVersion = $currentVersion;
        $this->contentType = $contentType;
        $this->relations = $relations;
        $this->path = $path;
    }
}
