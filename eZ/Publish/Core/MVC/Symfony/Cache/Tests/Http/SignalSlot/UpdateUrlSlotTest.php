<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentSignal;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\UpdateUrlSlot;
use eZ\Publish\Core\SignalSlot\Signal\URLService\UpdateUrlSignal;

class UpdateUrlSlotTest extends AbstractPurgeAllSlotTest
{
    /**
     * {@inheritdoc}
     */
    public static function createSignal()
    {
        return new UpdateUrlSignal([
            'urlId' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getReceivedSignalClasses()
    {
        return [
            UpdateContentSignal::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSlotClass()
    {
        return UpdateUrlSlot::class;
    }
}
