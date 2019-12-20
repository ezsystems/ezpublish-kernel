<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class Thumbnail extends ValueObject
{
    /**
     * Can be target URL or Base64 data (or anything else).
     *
     * @var string
     */
    protected $resource;

    /** @var int|null */
    protected $width;

    /** @var int|null */
    protected $height;

    /** @var string|null */
    protected $mimeType;
}
