<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Section;

use eZ\Publish\API\Repository\Events\Section\AssignSectionEvent as AssignSectionEventInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section;
use Symfony\Contracts\EventDispatcher\Event;

final class AssignSectionEvent extends Event implements AssignSectionEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\Section */
    private $section;

    public function __construct(
        ContentInfo $contentInfo,
        Section $section
    ) {
        $this->contentInfo = $contentInfo;
        $this->section = $section;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getSection(): Section
    {
        return $this->section;
    }
}
