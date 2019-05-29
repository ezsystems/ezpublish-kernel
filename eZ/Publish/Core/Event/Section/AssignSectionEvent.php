<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Section;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\Event\AfterEvent;

final class AssignSectionEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.section.assign';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private $contentInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Section
     */
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
