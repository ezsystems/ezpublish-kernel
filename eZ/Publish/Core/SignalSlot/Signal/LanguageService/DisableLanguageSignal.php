<?php

/**
 * DisableLanguageSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DisableLanguageSignal class.
 */
class DisableLanguageSignal extends Signal
{
    /**
     * LanguageId.
     *
     * @var mixed
     */
    public $languageId;
}
