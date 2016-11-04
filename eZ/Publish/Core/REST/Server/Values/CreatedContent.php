<?php

/**
 * File containing the CreatedContent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created Content.
 */
class CreatedContent extends ValueObject
{
    /**
     * The created content.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestContent
     */
    public $content;
}
