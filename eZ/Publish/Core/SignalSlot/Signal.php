<?php
/**
 * File containing the SignalDispatcher class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Base class for Signals. All Signals must derive this class.
 *
 * A Signal must always be fully export and re-creatable. It must therefore not
 * depend on external object references, resources or similar.
 *
 * @internal
 */
abstract class Signal extends ValueObject
{
}
