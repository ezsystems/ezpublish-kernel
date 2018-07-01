<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\URLService as APIURLService;
use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\Signal\URLService\UpdateUrlSignal;
use eZ\Publish\Core\SignalSlot\URLService;

class URLServiceTest extends ServiceTest
{
    public function serviceProvider()
    {
        $url = $this->getApiUrl(12, 'http://ez.no');

        return [
            [
                'updateUrl',
                [
                    $url,
                    new URLUpdateStruct([
                        'url' => 'http://ezplatform.com',
                    ]),
                ],
                $this->getApiUrl(12, 'http://ezplatform.com', true),
                1,
                UpdateUrlSignal::class,
                ['urlId' => $url->id, 'urlChanged' => true],
            ],
            [
                'updateUrl',
                [
                    $url,
                    new URLUpdateStruct([
                        'isValid' => false,
                    ]),
                ],
                $this->getApiUrl(12, 'http://ez.no', false),
                1,
                UpdateUrlSignal::class,
                ['urlId' => $url->id, 'urlChanged' => false],
            ],
            [
                'createUpdateStruct',
                [],
                new URLUpdateStruct(),
                0,
            ],
            [
                'findUrls',
                [new URLQuery()],
                new SearchResult(),
                0,
            ],
            [
                'loadById',
                [12],
                $url,
                0,
            ],
            [
                'loadByUrl',
                ['http://ez.no'],
                $url,
                0,
            ],
        ];
    }

    protected function getServiceMock()
    {
        return $this->createMock(APIURLService::class);
    }

    protected function getSignalSlotService($innerService, SignalDispatcher $dispatcher)
    {
        return new URLService($innerService, $dispatcher);
    }

    private function getApiUrl($id = null, $url = null, $isValid = false)
    {
        return new URL([
            'id' => $id,
            'url' => $url,
            'isValid' => $isValid,
        ]);
    }
}
