<?php
/**
 * File containing the SignalEvent class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\Core\Repository\SignalSlot\Signal;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is sent whenever a Signal is emitted by SignalSlot repository services.
 * It contains the Signal object.
 */
class SignalEvent extends Event
{
    /**
     * @var \eZ\Publish\Core\Repository\SignalSlot\Signal
     */
    private $signal;

    public function __construct( Signal $signal )
    {
        $this->signal = $signal;
    }

    /**
     * @return Signal
     */
    public function getSignal()
    {
        return $this->signal;
    }
}
