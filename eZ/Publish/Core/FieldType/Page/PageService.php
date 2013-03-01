<?php
/**
 * File containing the PageService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page;

use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use RuntimeException;

class PageService
{
    /**
     * Zone definition set in YAML config
     *
     * @var array
     */
    protected $zoneDefinition;

    /**
     * Block definition set in YAML config
     *
     * @var array
     */
    protected $blockDefinition;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageStorage\Gateway
     */
    protected $storageGateway;

    /**
     * Constructor
     *
     * @param array $zoneDefinition
     * @param array $blockDefinition
     */
    public function __construct( array $zoneDefinition = array(), array $blockDefinition = array() )
    {
        $this->zoneDefinition = $zoneDefinition;
        $this->blockDefinition = $blockDefinition;
    }

    /**
     * Returns zone definition as an array
     *
     * @return array
     */
    public function getZoneDefinition()
    {
        return $this->zoneDefinition;
    }

    /**
     * Returns block definition as an array
     *
     * @return array
     */
    public function getBlockDefinition()
    {
        return $this->blockDefinition;
    }

    /**
     * Returns list of available zone definitions
     *
     * @return array
     */
    public function getAvailableZoneTypes()
    {
        return array_keys( $this->zoneDefinition );
    }

    /**
     * Injects the storage gateway to interact with.
     *
     * @param \eZ\Publish\Core\FieldType\Page\PageStorage\Gateway $storageGateway
     */
    public function setStorageGateway( Gateway $storageGateway )
    {
        $this->storageGateway = $storageGateway;
    }

    /**
     * Checks if storage gateway has already been injected or not.
     *
     * @return bool
     */
    public function hasStorageGateway()
    {
        return isset( $this->storageGateway );
    }

    /**
     * @throws \RuntimeException If storage gateway is not set.
     *
     * @return \eZ\Publish\Core\FieldType\Page\PageStorage\Gateway
     */
    protected function getStorageGateway()
    {
        if ( !$this->hasStorageGateway() )
            throw new RuntimeException( 'Missing storage gateway for Page field type.' );

        return $this->storageGateway;
    }

    /**
     * Returns valid items (that are to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getValidBlockItems( Block $block )
    {
        return $this->getStorageGateway()->getValidBlockItems( $block );
    }

    /**
     * Returns queued items (the next to be displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getWaitingBlockItems( Block $block )
    {
        return $this->getStorageGateway()->getWaitingBlockItems( $block );
    }

    /**
     * Returns archived items (that were previously displayed), for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getArchivedBlockItems( Block $block )
    {
        return $this->getStorageGateway()->getArchivedBlockItems( $block );
    }
}
