<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher;

/**
 * @internal
 */
interface ConfigurableMatcherFactoryInterface extends MatcherFactoryInterface
{
    public function setMatchConfig(array $matchConfig): void;
}
