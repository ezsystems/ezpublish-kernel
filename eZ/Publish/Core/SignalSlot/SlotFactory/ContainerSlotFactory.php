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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Slot factory that is able to lookup slots based on identifier.
 */
class ContainerSlotFactory extends SlotFactory implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     */
    public function __construct()
    {
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
        if ( $this->container === null || !$this->container->has( $slotIdentifier ) )
            throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'slot', $slotIdentifier );

        return $this->container->get( $slotIdentifier );
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer( ContainerInterface $container = null )
    {
        $this->container = $container;
    }
}
