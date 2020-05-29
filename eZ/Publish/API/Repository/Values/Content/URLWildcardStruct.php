<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

class URLWildcardStruct extends ValueObject
{
    /** @var string */
    public $destinationUrl;

    /** @var string */
    public $sourceUrl;

    /** @var bool */
    public $forward;
}
