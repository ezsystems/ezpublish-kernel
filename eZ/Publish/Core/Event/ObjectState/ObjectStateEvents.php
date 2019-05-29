<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

final class ObjectStateEvents
{
    public const CREATE_OBJECT_STATE_GROUP = CreateObjectStateGroupEvent::NAME;
    public const BEFORE_CREATE_OBJECT_STATE_GROUP = BeforeCreateObjectStateGroupEvent::NAME;
    public const UPDATE_OBJECT_STATE_GROUP = UpdateObjectStateGroupEvent::NAME;
    public const BEFORE_UPDATE_OBJECT_STATE_GROUP = BeforeUpdateObjectStateGroupEvent::NAME;
    public const DELETE_OBJECT_STATE_GROUP = DeleteObjectStateGroupEvent::NAME;
    public const BEFORE_DELETE_OBJECT_STATE_GROUP = BeforeDeleteObjectStateGroupEvent::NAME;
    public const CREATE_OBJECT_STATE = CreateObjectStateEvent::NAME;
    public const BEFORE_CREATE_OBJECT_STATE = BeforeCreateObjectStateEvent::NAME;
    public const UPDATE_OBJECT_STATE = UpdateObjectStateEvent::NAME;
    public const BEFORE_UPDATE_OBJECT_STATE = BeforeUpdateObjectStateEvent::NAME;
    public const SET_PRIORITY_OF_OBJECT_STATE = SetPriorityOfObjectStateEvent::NAME;
    public const BEFORE_SET_PRIORITY_OF_OBJECT_STATE = BeforeSetPriorityOfObjectStateEvent::NAME;
    public const DELETE_OBJECT_STATE = DeleteObjectStateEvent::NAME;
    public const BEFORE_DELETE_OBJECT_STATE = BeforeDeleteObjectStateEvent::NAME;
    public const SET_CONTENT_STATE = SetContentStateEvent::NAME;
    public const BEFORE_SET_CONTENT_STATE = BeforeSetContentStateEvent::NAME;
}
