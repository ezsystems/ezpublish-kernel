<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

/**
 * If a test implements this interface, it will be given the opportunity to add expectations to the purgeForContent
 * invocation mocker used by the test, for instance to test which contentId was passed to purgeForContent.
 */
interface PurgeForContentExpectation
{
    public static function getContentId();

    public function testReceivePurgesCacheForContent($signal);
}
