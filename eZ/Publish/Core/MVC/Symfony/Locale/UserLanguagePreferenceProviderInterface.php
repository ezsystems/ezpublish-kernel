<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Locale;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides list of user-preferred languages.
 */
interface UserLanguagePreferenceProviderInterface
{
    /**
     * Return a list of user's browser preferred locales directly from Accept-Language header.
     *
     * @param \Symfony\Component\HttpFoundation\Request request to retrieve information from, use current if null
     *
     * @return string[]
     */
    public function getPreferredLocales(Request $request = null): array;

    /**
     * List of eZ Language codes.
     *
     * @return string[]
     */
    public function getPreferredLanguages(): array;
}
