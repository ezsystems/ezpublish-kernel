<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Model class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;

/**
 * Base class for value objects that represent a model.
 *
 * Model is the root entity of a domain within a eZ Platform module, e.g. Content model/module.
 *
 * @todo Define base needs for models (see CBA branch)
 */
abstract class Model extends ValueObject
{
}
