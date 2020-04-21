<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Specification\Content;

use eZ\Publish\API\Repository\Values\Content\Content;

final class ContentTypeSpecification implements ContentSpecification
{
    /**
     * @var string
     */
    private $expectedType;

    public function __construct(string $expectedType)
    {
        $this->expectedType = $expectedType;
    }

    public function isSatisfiedBy(Content $content): bool
    {
        return $content->getContentType()->identifier === $this->expectedType;
    }
}
