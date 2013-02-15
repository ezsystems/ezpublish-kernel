<?php
/**
 * UpdateLanguageNameSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateLanguageNameSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LanguageService
 */
class UpdateLanguageNameSignal extends Signal
{
    /**
     * LanguageId
     *
     * @var mixed
     */
    public $languageId;

    /**
     * NewName
     *
     * @var mixed
     */
    public $newName;
}
