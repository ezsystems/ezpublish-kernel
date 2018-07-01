<?php

/**
 * AddRelationSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AddRelationSignal class.
 */
class AddRelationSignal extends Signal
{
    /**
     * Content ID.
     *
     * @var mixed
     */
    public $srcContentId;

    /**
     * Version Number.
     *
     * @var int
     */
    public $srcVersionNo;

    /**
     * Content ID.
     *
     * @var mixed
     */
    public $dstContentId;
}
