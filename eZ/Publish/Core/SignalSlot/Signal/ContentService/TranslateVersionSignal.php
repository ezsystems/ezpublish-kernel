<?php

/**
 * TranslateVersionSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * TranslateVersionSignal class.
 * @deprecated since 7.5. Will be removed in 8.0 as it is no longer used.
 */
class TranslateVersionSignal extends Signal
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

    /**
     * UserId.
     *
     * @var mixed|null
     */
    public $userId;
}
