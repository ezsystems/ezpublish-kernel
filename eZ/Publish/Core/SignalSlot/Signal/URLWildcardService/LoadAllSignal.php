<?php
/**
 * LoadAllSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLWildcardService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadAllSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLWildcardService
 */
class LoadAllSignal extends Signal
{
    /**
     * Offset
     *
     * @var mixed
     */
    public $offset;

    /**
     * Limit
     *
     * @var mixed
     */
    public $limit;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $offset
     * @param mixed $limit
     */
    public function __construct( $offset, $limit )
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }
}

