<?php
/**
 * DisableLanguageSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DisableLanguageSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LanguageService
 */
class DisableLanguageSignal extends Signal
{
    /**
     * LanguageId
     *
     * @var mixed
     */
    public $languageId;
}
