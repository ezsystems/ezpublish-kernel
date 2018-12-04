<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RemoveContentTypeDraftTranslationSignal class.
 */
class RemoveContentTypeDraftTranslationSignal extends Signal
{
    /**
     * ContentTypeDraftId.
     *
     * @var mixed
     */
    public $contentTypeDraftId;

    /**
     * Language Code List of the removed translations.
     *
     * @var string[]
     */
    public $languageCodes;
}
