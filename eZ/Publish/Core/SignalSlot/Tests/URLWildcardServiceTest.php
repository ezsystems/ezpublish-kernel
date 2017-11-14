<?php

/**
 * File containing the URLWildcardServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\URLWildcardService as APIURLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\URLWildcardService;
use eZ\Publish\Core\SignalSlot\Signal\URLWildcardService as URLWildcardServiceSignals;

class URLWildcardServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIURLWildcardService::class);
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
            array(
                'id' => $wildcardId,
                'sourceUrl' => $sourceUrl,
                'destinationUrl' => $destinationUrl,
                'forward' => $forward,
            )
        );

        return array(
            array(
                'create',
                array($sourceUrl, $destinationUrl, $forward),
                $wildcard,
                1,
                URLWildcardServiceSignals\CreateSignal::class,
                array('urlWildcardId' => $wildcardId),
            ),
            array(
                'remove',
                array($wildcard),
                null,
                1,
                URLWildcardServiceSignals\RemoveSignal::class,
                array('urlWildcardId' => $wildcardId),
            ),
            array(
                'load',
                array($wildcardId),
                $wildcard,
                0,
            ),
            array(
                'loadAll',
                array(0, 1),
                array($wildcard),
                0,
            ),
            array(
                'translate',
                array($sourceUrl),
                new URLWildcardTranslationResult(
                    array(
                        'uri' => $destinationUrl,
                        'forward' => $forward,
                    )
                ),
                1,
                URLWildcardServiceSignals\TranslateSignal::class,
                array('url' => $sourceUrl),
            ),
        );
    }
}
