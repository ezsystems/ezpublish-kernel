<?php

/**
 * DeleteLanguageSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteLanguageSignal class.
 */
class DeleteLanguageSignal extends Signal
{
    /**
     * LanguageId.
     *
     * @var mixed
     */
    public $languageId;
}
