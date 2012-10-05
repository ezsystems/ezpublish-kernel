<?php
/**
 * CreateUrlAliasSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateUrlAliasSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLAliasService
 */
class CreateUrlAliasSignal extends Signal
{
    /**
     * Location
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;

    /**
     * Path
     *
     * @var mixed
     */
    public $path;

    /**
     * LanguageCode
     *
     * @var mixed
     */
    public $languageCode;

    /**
     * Forwarding
     *
     * @var mixed
     */
    public $forwarding;

    /**
     * AlwaysAvailable
     *
     * @var mixed
     */
    public $alwaysAvailable;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Location $location
     * @param mixed $path
     * @param mixed $languageCode
     * @param mixed $forwarding
     * @param mixed $alwaysAvailable
     */
    public function __construct( $location, $path, $languageCode, $forwarding, $alwaysAvailable )
    {
        $this->location = $location;
        $this->path = $path;
        $this->languageCode = $languageCode;
        $this->forwarding = $forwarding;
        $this->alwaysAvailable = $alwaysAvailable;
    }
}

