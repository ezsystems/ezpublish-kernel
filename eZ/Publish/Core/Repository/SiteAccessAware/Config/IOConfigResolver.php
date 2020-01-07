<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware\Config;

use eZ\Publish\Core\IO\IOConfig;

/**
 * @internal
 */
final class IOConfigResolver implements IOConfig
{
    /** @var string */
    private $storageDir;

    public function __construct(string $storageDir)
    {
        $this->storageDir = $storageDir;
    }

    public function getRootDir(): string
    {
        return $this->storageDir;
    }

    public function getLegacyUrlPrefix(): string
    {
        return $this->storageDir;
    }

    public function getUrlPrefix(): string
    {
        return $this->storageDir;
    }
}
