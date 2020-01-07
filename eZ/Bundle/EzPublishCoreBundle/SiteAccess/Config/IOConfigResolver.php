<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config;

use eZ\Publish\Core\IO\IOConfig;

/**
 * @internal
 */
final class IOConfigResolver implements IOConfig
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\ComplexConfigProcessor */
    private $complexConfigProcessor;

    public function __construct(
        ComplexConfigProcessor $complexConfigProcessor
    ) {
        $this->complexConfigProcessor = $complexConfigProcessor;
    }

    public function getRootDir(): string
    {
        return $this->complexConfigProcessor->processComplexSetting('io.root_dir');
    }

    public function getLegacyUrlPrefix(): string
    {
        return $this->complexConfigProcessor->processComplexSetting('io.legacy_url_prefix');
    }

    public function getUrlPrefix(): string
    {
        return $this->complexConfigProcessor->processComplexSetting('io.url_prefix');
    }
}
