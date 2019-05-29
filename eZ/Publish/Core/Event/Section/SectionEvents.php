<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Section;

final class SectionEvents
{
    public const CREATE_SECTION = CreateSectionEvent::NAME;
    public const BEFORE_CREATE_SECTION = BeforeCreateSectionEvent::NAME;
    public const UPDATE_SECTION = UpdateSectionEvent::NAME;
    public const BEFORE_UPDATE_SECTION = BeforeUpdateSectionEvent::NAME;
    public const ASSIGN_SECTION = AssignSectionEvent::NAME;
    public const BEFORE_ASSIGN_SECTION = BeforeAssignSectionEvent::NAME;
    public const ASSIGN_SECTION_TO_SUBTREE = AssignSectionToSubtreeEvent::NAME;
    public const BEFORE_ASSIGN_SECTION_TO_SUBTREE = BeforeAssignSectionToSubtreeEvent::NAME;
    public const DELETE_SECTION = DeleteSectionEvent::NAME;
    public const BEFORE_DELETE_SECTION = BeforeDeleteSectionEvent::NAME;
}
