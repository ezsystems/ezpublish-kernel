<?php
/**
 * File containing the SignalDispatcher class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;
use Closure;

/**
 * A generic slot made to be able to be catch all on content & Location related signals.
 */
class LegacySlotTiein extends Slot
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @param \Closure $legacyKernelClosure
     */
    public function __construct( Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
    }

    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     * @return void
     */
    public function receive( Signal $signal )
    {
        $kernel = $this->getLegacyKernel();
        $kernel->runCallback( function() use( $signal )
            {
                // @todo Split in logical slots instead of using one for all legacy tie ins?
                // @todo Needs review in terms of what needs to be updated and how
                if ( $signal instanceof Signal\ContentService\PublishVersionSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    $object = \eZContentObject::fetch( $signal->contentId );
                    \eZSearch::addObject( $object, false );
                }
                elseif ( $signal instanceof Signal\ContentService\DeleteContentSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    $object = \eZContentObject::fetch( $signal->contentId );
                    \eZSearch::removeObject( $object, false );
                }
                elseif ( $signal instanceof Signal\ContentService\DeleteVersionSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    $object = \eZContentObject::fetch( $signal->contentId );
                    \eZSearch::removeObject( $object, false );
                    \eZSearch::addObject( $object, false );
                }
                elseif ( $signal instanceof Signal\ContentService\CopyContentSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->dstContentId );
                    $object = \eZContentObject::fetch( $signal->dstContentId );
                    \eZSearch::addObject( $object, false );
                }
                elseif ( $signal instanceof Signal\LocationService\CreateLocationSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    $object = \eZContentObject::fetch( $signal->contentId );
                    \eZSearch::addNodeAssignment( $object->mainNodeID(), $signal->contentId, $signal->locationId );
                }
                elseif ( $signal instanceof Signal\LocationService\DeleteLocationSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    \eZSearch::removeNodes( array( $signal->locationId ) );
                }
                elseif ( $signal instanceof Signal\LocationService\UpdateLocationSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                }
                elseif ( $signal instanceof Signal\LocationService\UnhideLocationSignal )
                {
                    $node = \eZContentObjectTreeNode::fetch( $signal->locationId );
                    \eZContentObjectTreeNode::clearViewCacheForSubtree( $node );
                    \eZSearch::updateNodeVisibility( $signal->locationId, 'show' );
                }
                elseif ( $signal instanceof Signal\LocationService\HideLocationSignal )
                {
                    $node = \eZContentObjectTreeNode::fetch( $signal->locationId );
                    \eZContentObjectTreeNode::clearViewCacheForSubtree( $node );
                    \eZSearch::updateNodeVisibility( $signal->locationId, 'hide' );
                }
                elseif ( $signal instanceof Signal\LocationService\SwapLocationSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->content1Id );
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->content2Id );
                    \eZSearch::swapNode( $signal->location1Id, $signal->location2Id );
                }
                elseif ( $signal instanceof Signal\LocationService\MoveSubtreeSignal )
                {
                    $node = \eZContentObjectTreeNode::fetch( $signal->locationId );
                    \eZContentObjectTreeNode::clearViewCacheForSubtree( $node );
                    // @todo What about eZSearch in this case?
                }
                elseif ( $signal instanceof Signal\ObjectStateService\SetContentStateSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    \eZSearch::updateObjectState( $signal->contentId, array( $signal->objectStateId ) );
                }
                elseif ( $signal instanceof Signal\SectionService\AssignSectionSignal )
                {
                    \eZContentCacheManager::clearContentCacheIfNeeded( $signal->contentId );
                    \eZSearch::updateObjectsSection( $signal->contentId, $signal->sectionId );
                }

            },
            false
        );
    }

    /**
     * Returns the legacy kernel object.
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        if ( $this->legacyKernelClosure instanceof Closure )
        {
            $legacyKernelClosure = $this->legacyKernelClosure;
            $this->legacyKernelClosure = $legacyKernelClosure();
        }
        return $this->legacyKernelClosure;
    }
}

