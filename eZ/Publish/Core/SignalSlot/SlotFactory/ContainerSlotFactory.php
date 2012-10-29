<?php
/**
 * File containing the ContainerSlotFactory class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\SlotFactory;
use eZ\Publish\Core\SignalSlot\SlotFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Slot factory that is able to lookup slots based on identifier.
 */
class ContainerSlotFactory extends SlotFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * Returns a Slot with the given $slotIdentifier
     *
     * @param string $slotIdentifier
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException When no slot is found
     * @return \eZ\Publish\Core\SignalSlot\Slot
     */
    public function getSlot( $slotIdentifier )
    {
        if ( !$this->container->has( $slotIdentifier ) )
            throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'slot', $slotIdentifier );

        return $this->container->get( $slotIdentifier );
    }
}
