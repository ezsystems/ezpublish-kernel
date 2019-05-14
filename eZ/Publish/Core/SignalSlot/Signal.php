<?php

/**
 * File containing the SignalDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for Signals. All Signals must derive this class.
 *
 * A Signal must always be fully export and re-creatable. It must therefore not
 * depend on external object references, resources or similar.
 */
abstract class Signal extends ValueObject
{
}
