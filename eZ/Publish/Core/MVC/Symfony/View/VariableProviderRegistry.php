<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\SPI\MVC\View\VariableProvider;

interface VariableProviderRegistry
{
    public function setTwigVariableProvider(VariableProvider $twigVariableProvider): void;

    public function getTwigVariableProvider(string $identifier): VariableProvider;

    public function hasTwigVariableProvider(string $identifier): bool;
}
