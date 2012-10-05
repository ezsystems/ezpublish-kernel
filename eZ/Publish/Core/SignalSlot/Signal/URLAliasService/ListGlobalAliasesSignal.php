<?php
/**
 * ListGlobalAliasesSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * ListGlobalAliasesSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\URLAliasService
 */
class ListGlobalAliasesSignal extends Signal
{
    /**
     * LanguageCode
     *
     * @var mixed
     */
    public $languageCode;

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

}

