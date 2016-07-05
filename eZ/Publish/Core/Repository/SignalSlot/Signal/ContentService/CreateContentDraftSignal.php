<?php

/**
 * CreateContentDraftSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * CreateContentDraftSignal class.
 */
class CreateContentDraftSignal extends Signal
{
    /**
     * ContentId.
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

    /**
     * UserId.
     *
     * @var mixed
     */
    public $userId;
}
