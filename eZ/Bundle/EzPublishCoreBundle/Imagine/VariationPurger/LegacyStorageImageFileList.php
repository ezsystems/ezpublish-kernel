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
 * The returned items are uris to files, e.g. var/ezdemo_site/storage/images/...
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

    public function __construct( DatabaseHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return '/' . $this->item;
    }

    public function next()
    {
        $this->cursor++;
        $this->item = $this->statement->fetchColumn( 0 );
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
        $this->cursor = 0;

        $selectQuery = $this->dbHandler->createSelectQuery();
        $selectQuery->select( 'filepath' )->from( $this->dbHandler->quoteTable( 'ezimagefile' ) );
        $this->statement = $selectQuery->prepare();
        $this->statement->execute();
        $this->item = $this->statement->fetchColumn( 0 );
    }

    public function count()
    {
        return $this->statement->rowCount();
    }
}
