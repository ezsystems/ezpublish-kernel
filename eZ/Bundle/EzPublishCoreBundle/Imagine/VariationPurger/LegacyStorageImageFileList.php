<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;

/**
 * Iterator for entries in legacy's ezimagefile table.
 *
 * The returned items are id of Image BinaryFile (ez-mountains/mount-aconcagua/605-1-eng-GB/Mount-Aconcagua.jpg).
 */
class LegacyStorageImageFileList implements ImageFileList
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $dbHandler;

    /**
     * Database statement
     * @var \PDOStatement
     */
    private $statement;

    /**
     * Last fetched item
     * @var mixed
     */
    private $item;

    /**
     * Iteration cursor on $statement
     * @var int
     */
    private $cursor;

    /**
     * The storage prefix used by legacy, usually the vardir + the 'storage' folder.
     * Example: var/ezdemo_site/storage
     * @var string
     */
    private $prefix;

    /**
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param string $storageDir Folder, relative to the root, where files are stored. Example: var/ezdemo_site/storage
     * @param string $imagesDir Folder where images are stored, within the storage dir. Example: 'images'
     */
    public function __construct( DatabaseHandler $dbHandler, $storageDir, $imagesDir )
    {
        $this->dbHandler = $dbHandler;
        $this->prefix = $storageDir . '/' . $imagesDir;
    }

    public function current()
    {
        return $this->item;
    }

    public function next()
    {
        $this->fetchRow();
    }

    public function key()
    {
        return $this->cursor;
    }

    public function valid()
    {
        return ( $this->cursor < $this->count() );
    }

    public function rewind()
    {
        $this->cursor = -1;

        $selectQuery = $this->dbHandler->createSelectQuery();
        $selectQuery->select( 'filepath' )->from( $this->dbHandler->quoteTable( 'ezimagefile' ) );
        $this->statement = $selectQuery->prepare();
        $this->statement->execute();
        $this->fetchRow();
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    /**
     * Fetches the next item from the resultset, moves the cursor forward, and removes the prefix from the image id
     */
    private function fetchRow()
    {
        $this->cursor++;
        $imageId = $this->statement->fetchColumn( 0 );
        if ( substr( $imageId, 0, strlen( $this->prefix ) ) == $this->prefix )
        {
            $imageId = ltrim( substr( $imageId, strlen( $this->prefix ) ), '/' );
        }

        $this->item = $imageId;
    }
}
