<?php

/**
 * File containing the URLWildcardServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\URLWildcardService;

class URLWildcardServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\URLWildcardService'
        );
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new URLWildcardService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $wildcardId = 42;
        $sourceUrl = '/cms';
        $destinationUrl = '/cxm';
        $forward = true;
        $wildcard = new URLWildcard(
            [
                'id' => $wildcardId,
                'sourceUrl' => $sourceUrl,
                'destinationUrl' => $destinationUrl,
                'forward' => $forward,
            ]
        );

        return [
            [
                'create',
                [$sourceUrl, $destinationUrl, $forward],
                $wildcard,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\URLWildcardService\CreateSignal',
                ['urlWildcardId' => $wildcardId],
            ],
            [
                'remove',
                [$wildcard],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\URLWildcardService\RemoveSignal',
                ['urlWildcardId' => $wildcardId],
            ],
            [
                'load',
                [$wildcardId],
                $wildcard,
                0,
            ],
            [
                'loadAll',
                [0, 1],
                [$wildcard],
                0,
            ],
            [
                'translate',
                [$sourceUrl],
                new URLWildcardTranslationResult(
                    [
                        'uri' => $destinationUrl,
                        'forward' => $forward,
                    ]
                ),
                1,
                'eZ\Publish\Core\SignalSlot\Signal\URLWildcardService\TranslateSignal',
                ['url' => $sourceUrl],
            ],
        ];
    }
}
