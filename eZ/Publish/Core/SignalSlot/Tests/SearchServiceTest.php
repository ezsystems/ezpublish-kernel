<?php

/**
 * File containing the SearchServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\SearchService as APISearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\SearchService;

class SearchServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APISearchService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new SearchService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $languageFilter = ['languages' => ['fre-FR']];
        $content = $this->getContent(
            $this->getVersionInfo(
                $this->getContentInfo(42, md5(__METHOD__)),
                4
            )
        );
        $criterion = new Visibility(Visibility::VISIBLE);

        return [
            [
                'findContent',
                [
                    new Query(),
                    $languageFilter,
                    false,
                ],
                new SearchResult(['totalCount' => 0]),
                0,
            ],
            [
                'findContentInfo',
                [
                    new Query(),
                    $languageFilter,
                    false,
                ],
                new SearchResult(['totalCount' => 0]),
                0,
            ],
            [
                'findSingle',
                [
                    $criterion,
                    $languageFilter,
                    false,
                ],
                $content,
                0,
            ],
            [
                'findLocations',
                [
                    new LocationQuery(),
                    $languageFilter,
                    false,
                ],
                new SearchResult(['totalCount' => 0]),
                0,
            ],
            [
                'suggest',
                [
                    'awesome',
                    [],
                    20,
                    $criterion,
                ],
                null,
                0,
            ],
        ];
    }
}
