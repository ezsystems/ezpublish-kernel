<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLAlias;

final class URLAliasEvents
{
    public const CREATE_URL_ALIAS = CreateUrlAliasEvent::NAME;
    public const BEFORE_CREATE_URL_ALIAS = BeforeCreateUrlAliasEvent::NAME;
    public const CREATE_GLOBAL_URL_ALIAS = CreateGlobalUrlAliasEvent::NAME;
    public const BEFORE_CREATE_GLOBAL_URL_ALIAS = BeforeCreateGlobalUrlAliasEvent::NAME;
    public const REMOVE_ALIASES = RemoveAliasesEvent::NAME;
    public const BEFORE_REMOVE_ALIASES = BeforeRemoveAliasesEvent::NAME;
    public const REFRESH_SYSTEM_URL_ALIASES_FOR_LOCATION = RefreshSystemUrlAliasesForLocationEvent::NAME;
    public const BEFORE_REFRESH_SYSTEM_URL_ALIASES_FOR_LOCATION = BeforeRefreshSystemUrlAliasesForLocationEvent::NAME;
}
