<?php

/**
 * CreateSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\URLWildcardService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateSignal class.
 */
class CreateSignal extends Signal
{
    /**
     * UrlWildcardId.
     *
     * @var mixed
     */
    public $urlWildcardId;
}
