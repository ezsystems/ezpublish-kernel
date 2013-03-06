<?php
/**
 * File containing the Page Zone class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

use OutOfBoundsException;

/**
 * @property-read string $id Zone Id.
 * @property-read string $identifier Zone Identifier.
 * @property-read string $action Action to be executed. Can be either "add", "modify" or "remove" (see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants)
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Block[] $blocks Array of blocks, numerically indexed.
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Block[] $blocksById Array of blocks, indexed by their Id.
 */
class Zone extends Base
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Block[]
     */
    protected $blocks = array();

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Block[]
     */
    protected $blocksById = array();

    /**
     * @var array
     */
    private $blockKeys;

    /**
     * Zone Id.
     *
     * @var string
     */
    protected $id;

    /**
     * Zone identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * @see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants
     *
     * @var string
     */
    protected $action;

    /**
     * Returns a block by numeric index.
     *
     * @param int $index
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     *
     * @throws \OutOfBoundsException If $index is invalid.
     */
    public function getBlockByIndex( $index )
    {
        if ( !isset( $this->blockKeys ) )
            $this->blockKeys = array_keys( $this->blocks );

        if ( !isset( $this->blockKeys[$index] ) )
            throw new OutOfBoundsException( "Could not find block with index #$index" );

        return $this->blocks[$this->blockKeys[$index]];
    }

    /**
     * {@inheritedDoc}
     */
    protected function init()
    {
        foreach ( $this->blocks as $block )
        {
            $this->blocksById[$block->id] = $block;
        }
    }
}
