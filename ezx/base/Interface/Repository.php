<?php
/**
 * Repository Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

namespace ezx\base;
interface Interface_Repository
{
    /**
     * Retrive objects by criteria
     *
     * @param Interface_RepositoryCriteria $criteria
     * @return array<object>
     * @throws \InvalidArgumentException
     */
    public function find( Interface_RepositoryCriteria $criteria );

    /**
     * Get an object by id.
     *
     * This is an alias for find() where query object to filter on id is built for you.
     * Hence it's assumed that all models will have an id column.
     *
     * @param string $type
     * @param int $id
     * @return object
     * @throws \InvalidArgumentException
     */
    public function load( $type, $id );

    /**
     * Store a model or collection of models in the repository
     *
     * @param ModelCollectionInterface|ModelInterface $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( Abstract_Model $object );

    /**
     * Delete a model or collection of models in the repository
     *
     * @param ModelCollectionInterface|ModelInterface $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function delete( Abstract_Model $object );

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
