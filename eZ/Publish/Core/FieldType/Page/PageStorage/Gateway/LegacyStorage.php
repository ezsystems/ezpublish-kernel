<?php
/**
 * File containing the LegacyStorage gateway class for Page field type.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;

use eZ\Publish\Core\FieldType\Page\PageStorage\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use RuntimeException;

class LegacyStorage extends Gateway
{
    const POOL_TABLE = 'ezm_pool';

    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof EzcDbHandler )
        {
            throw new RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getValidBlockItems( Block $block )
    {
        // TODO: Implement getValidBlockItems() method.
    }

    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getWaitingBlockItems( Block $block )
    {
        // TODO: Implement getWaitingBlockItems() method.
    }

    /**
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getArchivedBlockItems( Block $block )
    {
        // TODO: Implement getArchivedBlockItems() method.
    }
}
