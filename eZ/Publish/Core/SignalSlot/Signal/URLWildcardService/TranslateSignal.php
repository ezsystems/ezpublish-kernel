<?php

/**
 * TranslateSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\URLWildcardService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * TranslateSignal class.
 */
class TranslateSignal extends Signal
{
    /**
     * Url.
     *
     * @var mixed
     */
    public $url;
}
