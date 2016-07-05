<?php

/**
 * PublishVersionSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * PublishVersionSignal class.
 */
class PublishVersionSignal extends Signal
{
    /**
     * Content ID.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Version Number.
     *
     * @var int
     */
    public $versionNo;
}
