<?php
/**
 * Storage Engine Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base\Interfaces;
interface StorageEngine
{
    /**
     * Get Content Handler
     *
     * @return StorageEngine\ContentHandler
     */
    public function ContentHandler();

    /**
     * Get Content Handler
     *
     * @return StorageEngine\ContentTypeHandler
     */
    public function ContentTypeHandler();

    /**
     * Get Content Location Handler
     *
     * @return StorageEngine\ContentLocationHandler
     */
    public function ContentLocationHandler();

    /**
     * Get User Handler
     *
     * @return StorageEngine\UserHandler
     */
    public function UserHandler();

    /**
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction();

    /**
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit();

    /**
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback();
}
