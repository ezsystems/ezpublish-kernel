<?php

/**
 * File containing the ContentTypeInfoList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * ContentType list view model.
 */
class ContentTypeInfoList extends RestValue
{
    /**
     * Content types.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType[]
     */
    public $contentTypes;

    /**
     * Path which was used to fetch the list of content types.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType[] $contentTypes
     * @param string $path
     */
    public function __construct(array $contentTypes, $path)
    {
        $this->contentTypes = $contentTypes;
        $this->path = $path;
    }
}
