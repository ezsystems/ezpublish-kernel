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

}

