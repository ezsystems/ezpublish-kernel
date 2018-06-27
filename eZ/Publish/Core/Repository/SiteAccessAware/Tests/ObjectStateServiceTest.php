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

        // string $method, array $arguments, bool $return = true
        return [
            ['createObjectStateGroup', [$objectStateGroupCreateStruct]],
            ['updateObjectStateGroup', [$objectStateGroup, $objectStateGroupUpdateStruct]],
            ['deleteObjectStateGroup', [$objectStateGroup]],

            ['createObjectState', [$objectStateGroup, $objectStateCreateStruct]],
            ['updateObjectState', [$objectState, $objectStateUpdateStruct]],
            ['setPriorityOfObjectState', [$objectState, 4]],
            ['deleteObjectState', [$objectState]],

            ['setContentState', [$contentInfo, $objectStateGroup, $objectState]],
            ['getContentState', [$contentInfo, $objectStateGroup]],
            ['getContentCount', [$objectState]],

            ['newObjectStateGroupCreateStruct', ['locker']],
            ['newObjectStateGroupUpdateStruct', []],
            ['newObjectStateCreateStruct', ['locked']],
            ['newObjectStateUpdateStruct', []],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $objectStateGroup = new ObjectStateGroup();

        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [
            ['loadObjectStateGroup', [11, self::LANG_ARG], true, 1],
            ['loadObjectStateGroups', [50, 50, self::LANG_ARG], true, 2],
            ['loadObjectStates', [$objectStateGroup, self::LANG_ARG], true, 1],
            ['loadObjectState', [3, self::LANG_ARG], true, 1],
        ];
    }
}
