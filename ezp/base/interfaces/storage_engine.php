<?php
/**
 * Storage Engine Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezp\base;
interface StorageEngineInterface
{
    /**
     * Get Content Handler
     *
     * @return StorageEngine\ContentHandlerInterface
     */
    public function getContentHandler();

    /**
     * Get Content Handler
     *
     * @return StorageEngine\ContentTypeHandlerInterface
     */
    public function getContentTypeHandler();

    /**
     * Get Content Location Handler
     *
     * @return StorageEngine\ContentLocationHandlerInterface
     */
    public function getContentLocationHandler();

    /**
     * Get User Handler
     *
     * @return StorageEngine\UserHandlerInterface
     */
    public function getUserHandler();

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
