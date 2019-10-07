<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\ObjectStateService as APIService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\ObjectStateService;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup;

class ObjectStateServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return ObjectStateService::class;
    }

    public function providerForPassTroughMethods()
    {
        $objectStateGroupCreateStruct = new ObjectStateGroupCreateStruct();
        $objectStateGroupUpdateStruct = new ObjectStateGroupUpdateStruct();
        $objectStateGroup = new ObjectStateGroup();

        $objectStateCreateStruct = new ObjectStateCreateStruct();
        $objectStateUpdateStruct = new ObjectStateUpdateStruct();
        $objectState = new ObjectState();

        $contentInfo = new ContentInfo();

        // string $method, array $arguments, mixed $return = true
        return [
            ['createObjectStateGroup', [$objectStateGroupCreateStruct], $objectStateGroup],
            ['updateObjectStateGroup', [$objectStateGroup, $objectStateGroupUpdateStruct], $objectStateGroup],
            ['deleteObjectStateGroup', [$objectStateGroup], null],

            ['createObjectState', [$objectStateGroup, $objectStateCreateStruct], $objectState],
            ['updateObjectState', [$objectState, $objectStateUpdateStruct], $objectState],
            ['setPriorityOfObjectState', [$objectState, 4], null],
            ['deleteObjectState', [$objectState], null],

            ['setContentState', [$contentInfo, $objectStateGroup, $objectState], null],
            ['getContentState', [$contentInfo, $objectStateGroup], $objectState],
            ['getContentCount', [$objectState], 100],

            ['newObjectStateGroupCreateStruct', ['locker'], $objectStateGroupCreateStruct],
            ['newObjectStateGroupUpdateStruct', [], $objectStateGroupUpdateStruct],
            ['newObjectStateCreateStruct', ['locked'], $objectStateCreateStruct],
            ['newObjectStateUpdateStruct', [], $objectStateUpdateStruct],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $objectStateGroup = new ObjectStateGroup();
        $objectState = new ObjectState();

        // string $method, array $arguments, mixed $return, int $languageArgumentIndex
        return [
            ['loadObjectStateGroup', [11, self::LANG_ARG], $objectStateGroup, 1],
            ['loadObjectStateGroups', [50, 50, self::LANG_ARG], [$objectStateGroup], 2],
            ['loadObjectStates', [$objectStateGroup, self::LANG_ARG], [$objectState], 1],
            ['loadObjectState', [3, self::LANG_ARG], $objectState, 1],
        ];
    }
}
