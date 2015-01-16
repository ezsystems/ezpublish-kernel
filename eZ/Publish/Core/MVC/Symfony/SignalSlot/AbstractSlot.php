<?php
/**
 * File containing the AbstractLegacySlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SignalSlot;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;

/**
 * A abstract legacy slot covering common functions needed for legacy slots.
 */
abstract class AbstractSlot extends Slot
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
     */
    protected $httpCacheClearer;

    /**
     * @var \\eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger $httpCacheClearer
     */
    public function __construct( GatewayCachePurger $httpCacheClearer, ContentService $contentService )
    {
        $this->httpCacheClearer = $httpCacheClearer;
        $this->contentService = $contentService;
    }

    protected function getLocationId( $contentId )
    {
        return $this->contentService->loadContentInfo( $contentId )->mainLocationId;
    }

    public function receive( Signal $signal )
    {
        if ( !$this->supports( $signal ) )
        {
            return;
        }

        $this->httpCacheClearer->purgeForContent( $this->extractContentId( $signal ) );
    }

    /**
     * Extracts a Content ID from $signal
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return mixed A Content ID
     */
    abstract protected function extractContentId( Signal $signal );

    /**
     * Checks if $signal is supported by this handler
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return bool
     */
    abstract protected function supports( Signal $signal );
}
