<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\SPI\Persistence\Content\Location\Handler;

/**
 * A slot handling PublishVersionSignal.
 */
class PublishVersionSlot extends AbstractContentSlot
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    private $locationHandler;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface $purgeClient
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $spiLocationHandler
     */
    public function __construct(PurgeClientInterface $purgeClient, Handler $spiLocationHandler)
    {
        parent::__construct($purgeClient);
        $this->locationHandler = $spiLocationHandler;
    }

    /**
     * Default provides tags to clear content, relation, location, parent and sibling cache.
     *
     * Overload for tree operations where you also need to clear whole path.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal $signal
     *
     * @return array
     */
    protected function generateTags(Signal $signal)
    {
        $tags = parent::generateTags($signal);
        foreach ($this->locationHandler->loadLocationsByContent($signal->contentId) as $location) {
            // self
            $tags[] = 'location-' . $location->id;
            // children
            $tags[] = 'parent-' . $location->id;
            // parent
            $tags[] = 'location-' . $location->parentId;
            // siblings
            $tags[] = 'parent-' . $location->parentId;
        }

        return $tags;
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\ContentService\PublishVersionSignal;
    }
}
