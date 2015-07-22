<?php

/**
 * CreateUrlAliasSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\URLAliasService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateUrlAliasSignal class.
 */
class CreateUrlAliasSignal extends Signal
{
    /**
     * URL Alias ID.
     *
     * @var mixed
     */
    public $urlAliasId;
}
