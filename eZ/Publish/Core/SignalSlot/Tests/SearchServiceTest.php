<?php

/**
 * File containing the SearchServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

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
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\SearchService'
        );
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new SearchService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $languageFilter = array('languages' => array('fre-FR'));
        $content = $this->getContent(
            $this->getVersionInfo(
                $this->getContentInfo(42, md5(__METHOD__)),
                4
            )
        );
        $criterion = new Visibility(Visibility::VISIBLE);

        return array(
            array(
                'findContent',
                array(
                    new Query(),
                    $languageFilter,
                    false,
                ),
                new SearchResult(array('totalCount' => 0)),
                0,
            ),
            array(
                'findContentInfo',
                array(
                    new Query(),
                    $languageFilter,
                    false,
                ),
                new SearchResult(array('totalCount' => 0)),
                0,
            ),
            array(
                'findSingle',
                array(
                    $criterion,
                    $languageFilter,
                    false,
                ),
                $content,
                0,
            ),
            array(
                'findLocations',
                array(
                    new LocationQuery(),
                    $languageFilter,
                    false,
                ),
                new SearchResult(array('totalCount' => 0)),
                0,
            ),
            array(
                'suggest',
                array(
                    'awesome',
                    array(),
                    20,
                    $criterion,
                ),
                null,
                0,
            ),
        );
    }
}
