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
use OutOfBoundsException;
use SplObjectStorage;

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
     * Cached valid block items by block.
     *
     * @var \SplObjectStorage
     */
    protected $validBlockItems;

    /**
     * Cached last valid items, by block (one per block)
     *
     * @var \SplObjectStorage
     */
    protected $lastValidItems;

    /**
     * Cached waiting block items by block.
     *
     * @var \SplObjectStorage
     */
    protected $waitingBlockItems;

    /**
     * Cached archived block items by block.
     *
     * @var \SplObjectStorage
     */
    protected $archivedBlockItems;

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
        $this->validBlockItems = new SplObjectStorage();
        $this->lastValidItems = new SplObjectStorage();
        $this->waitingBlockItems = new SplObjectStorage();
        $this->archivedBlockItems = new SplObjectStorage();
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
     * Returns a zone definition for a given layout.
     * It consists of a configuration array for the given layout.
     *
     * @param string $layoutIdentifier
     *
     * @return array
     *
     * @throws \OutOfBoundsException If $layoutIdentifier is invalid
     */
    public function getZoneDefinitionByLayout( $layoutIdentifier )
    {
        if ( !isset( $this->zoneDefinition[$layoutIdentifier] ) )
            throw new OutOfBoundsException( "Could not find an ezpage zone definition block for given layout '$layoutIdentifier'" );

        return $this->zoneDefinition[$layoutIdentifier];
    }

    /**
     * Returns the template to use for given layout.
     *
     * @param string $layoutIdentifier
     * @return string
     */
    public function getLayoutTemplate( $layoutIdentifier )
    {
        $def = $this->getZoneDefinitionByLayout( $layoutIdentifier );
        return $def['template'];
    }

    /**
     * Checks if zone definition contains a layout having $layoutIdentifier as identifier.
     *
     * @param string $layoutIdentifier
     *
     * @return bool
     */
    public function hasZoneLayout( $layoutIdentifier )
    {
        return isset( $this->zoneDefinition[$layoutIdentifier] );
    }

    /**
     * Returns list of available zone layouts
     *
     * @return array
     */
    public function getAvailableZoneLayouts()
    {
        return array_keys( $this->zoneDefinition );
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
     * Returns a block definition for a given block identifier.
     *
     * @param string $blockIdentifier
     *
     * @return array
     *
     * @throws \OutOfBoundsException If $blockIdentifier is invalid.
     */
    public function getBlockDefinitionByIdentifier( $blockIdentifier )
    {
        if ( !isset( $this->blockDefinition[$blockIdentifier] ) )
            throw new OutOfBoundsException( "Could not find an ezpage block definition for given identifier '$blockIdentifier'" );

        return $this->blockDefinition[$blockIdentifier];
    }

    /**
     * Checks if block definition contains a block having $blockIdentifier as identifier.
     *
     * @param string $blockIdentifier
     *
     * @return bool
     */
    public function hasBlockDefinition( $blockIdentifier )
    {
        return isset( $this->blockDefinition[$blockIdentifier] );
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
        if ( isset( $this->validBlockItems[$block] ) )
            return $this->validBlockItems[$block];

        return $this->validBlockItems[$block] = $this->getStorageGateway()->getValidBlockItems( $block );
    }

    /**
     * Returns the last valid item, for a given block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]|null
     */
    public function getLastValidBlockItem( Block $block )
    {
        if ( isset( $this->lastValidItems[$block] ) )
            return $this->lastValidItems[$block];

        return $this->lastValidItems[$block] = $this->getStorageGateway()->getLastValidBlockItem( $block );
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
        if ( isset( $this->waitingBlockItems[$block] ) )
            return $this->waitingBlockItems[$block];

        return $this->waitingBlockItems[$block] = $this->getStorageGateway()->getWaitingBlockItems( $block );
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
        if ( isset( $this->archivedBlockItems[$block] ) )
            return $this->archivedBlockItems[$block];

        return $this->archivedBlockItems[$block] = $this->getStorageGateway()->getArchivedBlockItems( $block );
    }
}
