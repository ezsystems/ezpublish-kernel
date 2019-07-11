<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

interface BeforeDeleteRelationEvent extends BeforeEvent
{
    public function getSourceVersion(): VersionInfo;

    public function getDestinationContent(): ContentInfo;
}
