<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\BeforeDeleteVersionEvent as BeforeDeleteVersionEventInterface;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeDeleteVersionEvent extends Event implements BeforeDeleteVersionEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    public function __construct(VersionInfo $versionInfo)
    {
        $this->versionInfo = $versionInfo;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }
}
