<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Language;

final class LanguageEvents
{
    public const CREATE_LANGUAGE = CreateLanguageEvent::NAME;
    public const BEFORE_CREATE_LANGUAGE = BeforeCreateLanguageEvent::NAME;
    public const UPDATE_LANGUAGE_NAME = UpdateLanguageNameEvent::NAME;
    public const BEFORE_UPDATE_LANGUAGE_NAME = BeforeUpdateLanguageNameEvent::NAME;
    public const ENABLE_LANGUAGE = EnableLanguageEvent::NAME;
    public const BEFORE_ENABLE_LANGUAGE = BeforeEnableLanguageEvent::NAME;
    public const DISABLE_LANGUAGE = DisableLanguageEvent::NAME;
    public const BEFORE_DISABLE_LANGUAGE = BeforeDisableLanguageEvent::NAME;
    public const DELETE_LANGUAGE = DeleteLanguageEvent::NAME;
    public const BEFORE_DELETE_LANGUAGE = BeforeDeleteLanguageEvent::NAME;
}
