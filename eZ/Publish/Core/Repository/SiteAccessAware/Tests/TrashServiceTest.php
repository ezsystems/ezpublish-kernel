<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\TrashService as APIService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Trash\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
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
        $newLocation = new Location();
        $trashItem = new TrashItem();
        $query = new Query();
        $searchResult = new SearchResult();
        $trashItemDeleteResult = new TrashItemDeleteResult();
        $trashItemDeleteResultList = new TrashItemDeleteResultList();

        // string $method, array $arguments, bool $return = true
        return [
            ['loadTrashItem', [22], $trashItem],
            ['trash', [$location], $trashItem],
            ['recover', [$trashItem, $location], $newLocation],
            ['emptyTrash', [], $trashItemDeleteResultList],
            ['deleteTrashItem', [$trashItem], $trashItemDeleteResult],
            ['findTrashItems', [$query], $searchResult],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
