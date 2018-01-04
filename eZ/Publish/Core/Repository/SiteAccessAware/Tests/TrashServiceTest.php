<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\TrashService as APIService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Repository\SiteAccessAware\TrashService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;

class TrashServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return TrashService::class;
    }

    public function providerForPassTroughMethods()
    {
        $location = new Location();
        $trashItem = new TrashItem();
        $query = new Query();

        // string $method, array $arguments, bool $return = true
        return [
            ['loadTrashItem', [22]],
            ['trash', [$location]],
            ['recover', [$trashItem, $location]],
            ['emptyTrash', []],
            ['deleteTrashItem', [$trashItem]],
            ['findTrashItems', [$query]],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
