<?php

/**
 * File containing the Version class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Version view model.
 */
class Version extends RestValue
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    public $content;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    public $contentType;

    /** @var \eZ\Publish\API\Repository\Values\Content\Relation[] */
    public $relations;

    /**
     * Path used to load this content.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     * @param string $path
     */
    public function __construct(Content $content, ContentType $contentType, array $relations, $path = null)
    {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->relations = $relations;
        $this->path = $path;
    }
}
