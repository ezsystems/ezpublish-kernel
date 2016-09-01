<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * A reference to a value object.
 *
 * @property string $type
 * @property array $loadParameters
 */
class LoadableValueObjectReference extends ValueObject
{
    /**
     * The value object's type string. Ex: User, Content...
     * @var string
     */
    protected $type;

    /**
     * Parameters used to load the value object if applicable.
     * @var array
     */
    protected $loadParameters;
}
