<?php

/**
 * File containing the CreatedURLAlias class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created URLAlias.
 */
class CreatedURLAlias extends ValueObject
{
    /**
     * The created URL alias.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public $urlAlias;
}
