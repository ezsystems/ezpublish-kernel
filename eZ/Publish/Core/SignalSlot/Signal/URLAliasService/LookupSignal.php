<?php
/**
 * LookupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LookupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLAliasService
 */
class LookupSignal extends Signal
{
    /**
     * Url
     *
     * @var mixed
     */
    public $url;

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
     * @param mixed $url
     * @param mixed $languageCode
     */
    public function __construct( $url, $languageCode )
    {
        $this->url = $url;
        $this->languageCode = $languageCode;
    }
}

