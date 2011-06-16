<?php
/**
 * Storage Engine Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base;
interface Interface_StorageEngine
{
    /**
     * Get Content Handler
     *
     * @return \ezx\base\Interface_StorageEngine_ContentHandler
     */
    public function ContentHandler();

    /**
     * Get Content Handler
     *
     * @return \ezx\base\Interface_StorageEngine_ContentTypeHandler
     */
    public function ContentTypeHandler();

    /**
     * Get Content Location Handler
     *
     * @return \ezx\base\Interface_StorageEngine_ContentLocationHandler
     */
    public function ContentLocationHandler();

    /**
     * Get User Handler
     *
     * @return \ezx\base\Interface_StorageEngine_UserHandler
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
