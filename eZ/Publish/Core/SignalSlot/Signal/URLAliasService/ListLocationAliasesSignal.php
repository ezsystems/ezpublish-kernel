<?php
/**
 * ListLocationAliasesSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * ListLocationAliasesSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLAliasService
 */
class ListLocationAliasesSignal extends Signal
{
    /**
     * LocationId
     *
     * @var mixed
     */
    public $locationId;

    /**
     * Custom
     *
     * @var mixed
     */
    public $custom;

    /**
     * LanguageCode
     *
     * @var mixed
     */
    public $languageCode;

}

