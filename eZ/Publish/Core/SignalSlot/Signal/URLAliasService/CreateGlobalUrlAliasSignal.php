<?php
/**
 * CreateGlobalUrlAliasSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateGlobalUrlAliasSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLAliasService
 */
class CreateGlobalUrlAliasSignal extends Signal
{
    /**
     * Resource
     *
     * @var mixed
     */
    public $resource;

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

