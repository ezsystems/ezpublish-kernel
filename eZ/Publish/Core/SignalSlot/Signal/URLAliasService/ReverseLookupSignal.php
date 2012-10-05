<?php
/**
 * ReverseLookupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * ReverseLookupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLAliasService
 */
class ReverseLookupSignal extends Signal
{
    /**
     * Location
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;

    /**
     * LanguageCode
     *
     * @var mixed
     */
    public $languageCode;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Location $location
     * @param mixed $languageCode
     */
    public function __construct( $location, $languageCode )
    {
        $this->location = $location;
        $this->languageCode = $languageCode;
    }
}

