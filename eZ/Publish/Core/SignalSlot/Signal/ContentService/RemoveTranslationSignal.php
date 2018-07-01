<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

@trigger_error(
    sprintf(
        'The %s is deprecated since 6.13 and will be removed in 7.0. Use %s instead',
        RemoveTranslationSignal::class,
        DeleteTranslationSignal::class
    ),
    E_USER_DEPRECATED
);

/**
 * @deprecated since 6.13, use DeleteTranslationSignal
 */
class RemoveTranslationSignal extends DeleteTranslationSignal
{
}
