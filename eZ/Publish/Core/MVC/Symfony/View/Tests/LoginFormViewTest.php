<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use eZ\Publish\Core\MVC\Symfony\View\LoginFormView;
use eZ\Publish\Core\MVC\Symfony\View\View;

/**
 * @group mvc
 */
final class LoginFormViewTest extends AbstractViewTest
{
    protected function createViewUnderTest($template = null, array $parameters = [], $viewType = 'full'): View
    {
        return new LoginFormView($template, $parameters, $viewType);
    }

    protected function getAlwaysAvailableParams(): array
    {
        return [
            'last_username' => null,
            'error' => null,
        ];
    }
}
