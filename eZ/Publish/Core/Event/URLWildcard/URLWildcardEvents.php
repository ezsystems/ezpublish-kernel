<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLWildcard;

final class URLWildcardEvents
{
    public const CREATE = CreateEvent::NAME;
    public const BEFORE_CREATE = BeforeCreateEvent::NAME;
    public const REMOVE = RemoveEvent::NAME;
    public const BEFORE_REMOVE = BeforeRemoveEvent::NAME;
    public const TRANSLATE = TranslateEvent::NAME;
    public const BEFORE_TRANSLATE = BeforeTranslateEvent::NAME;
}
