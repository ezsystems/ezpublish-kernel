<?php

/**
 * File containing the VersatileScopeInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Configuration;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Allows a ConfigResolver to dynamically change their default scope.
 */
interface VersatileScopeInterface extends ConfigResolverInterface
{
    public function getDefaultScope(): string;

    public function setDefaultScope(string $scope): void;
}
