<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\View;

use eZ\Publish\Core\MVC\Symfony\View\View;

interface VariableProvider
{
    public function getIdentifier(): string;

    public function getTwigVariables(View $view, array $options = []): object;
}
