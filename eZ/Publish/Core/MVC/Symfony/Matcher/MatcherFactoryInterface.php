<?php

/**
 * File containing the MatcherFactory interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\View;

interface MatcherFactoryInterface
{
    /**
     * Checks if $valueObject has a usable configuration for $viewType.
     * If so, the configuration hash will be returned.
     *
     * $valueObject can be for example a Location or a Content object.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return array|null The matched configuration as a hash, containing template or controller to use, or null if not matched.
     */
    public function match(View $view);
}
