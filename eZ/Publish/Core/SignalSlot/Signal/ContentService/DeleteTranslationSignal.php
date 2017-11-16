<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteTranslationSignal emitted when a Content Object Translation gets deleted from all Versions.
 */
class DeleteTranslationSignal extends Signal
{
    /**
     * Content ID.
     *
     * @var int
     */
    public $contentId;

    /**
     * Language Code of the removed translation.
     *
     * @var string
     */
    public $languageCode;
}
