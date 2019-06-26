<?php

/**
 * File containing the RestUserGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * REST UserGroup, as received by /user/groups/<path>.
 */
class RestUserGroup extends RestValue
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    public $content;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    public $contentType;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    public $contentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\Relation[] */
    public $relations;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    public $mainLocation;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $mainLocation
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     */
    public function __construct(
        Content $content,
        ContentType $contentType,
        ContentInfo $contentInfo,
        Location $mainLocation,
        array $relations
    ) {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->contentInfo = $contentInfo;
        $this->mainLocation = $mainLocation;
        $this->relations = $relations;
    }
}
