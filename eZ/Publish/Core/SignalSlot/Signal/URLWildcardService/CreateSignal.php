<?php
/**
 * CreateSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLWildcardService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLWildcardService
 */
class CreateSignal extends Signal
{
    /**
     * SourceUrl
     *
     * @var mixed
     */
    public $sourceUrl;

    /**
     * DestinationUrl
     *
     * @var mixed
     */
    public $destinationUrl;

    /**
     * Foreward
     *
     * @var mixed
     */
    public $foreward;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $sourceUrl
     * @param mixed $destinationUrl
     * @param mixed $foreward
     */
    public function __construct( $sourceUrl, $destinationUrl, $foreward )
    {
        $this->sourceUrl = $sourceUrl;
        $this->destinationUrl = $destinationUrl;
        $this->foreward = $foreward;
    }
}

