<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Content item Value Object - a composite of Content and Type instances.
 *
 * @property-read \eZ\Publish\SPI\Persistence\Content $content
 * @property-read \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
 * @property-read \eZ\Publish\SPI\Persistence\Content\Type $type
 */
final class ContentItem extends ValueObject
{
    /** @var \eZ\Publish\SPI\Persistence\Content */
    protected $content;

    /** @var \eZ\Publish\SPI\Persistence\Content\ContentInfo */
    protected $contentInfo;

    /** @var \eZ\Publish\SPI\Persistence\Content\Type */
    protected $type;

    /**
     * @internal for internal use by Repository Storage abstraction
     */
    public function __construct(Content $content, ContentInfo $contentInfo, Type $type)
    {
        parent::__construct([]);
        $this->content = $content;
        $this->contentInfo = $contentInfo;
        $this->type = $type;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
