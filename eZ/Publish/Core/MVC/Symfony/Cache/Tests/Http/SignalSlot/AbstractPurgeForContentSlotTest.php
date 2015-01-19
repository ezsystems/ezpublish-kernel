<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

abstract class AbstractPurgeForContentSlotTest
    extends AbstractSlotTest
    implements PurgeForContentExpectation
{
    private static $contentId = 42;

    public function addPurgeForContentExpectations()
    {
        $this->cachePurgerMock
            ->expects( $this->once() )
            ->method( 'purgeForContent' )
            ->with( self::getContentId() );
    }

    /**
     * @return mixed
     */
    public static function getContentId()
    {
        return self::$contentId;
    }
}
