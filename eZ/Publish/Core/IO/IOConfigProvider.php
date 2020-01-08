<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\IO;

/**
 * Resolves IO complex settings.
 *
 * @internal
 */
interface IOConfigProvider
{
    public function getRootDir(): string;

    public function getLegacyUrlPrefix(): string;

    public function getUrlPrefix(): string;
}
