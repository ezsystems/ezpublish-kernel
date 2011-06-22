<?php
/**
 * Generic implementation for Proxy object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

namespace ezx\base;
class Proxy implements Interfaces\Proxy
{
    /**
     * @var Interfaces\Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $id;

    /**
     * Setup proxy object with enough info to be able to perform a load operation on the object it proxies.
     *
     * @param Interfaces\Repository $repository
     * @param string $type The type of object this Proxy object represent
     * @param int $id Primary id
     * @throws InvalidArgumentException If $id is not a int value above zero.
     */
    public function __construct( Interfaces\Repository $repository, $type, $id )
    {
        $this->repository = $repository;
        $this->type = $type;
        $this->id = (int) $id;
        if ( $this->id === 0 )
            throw new \InvalidArgumentException( "Id parameter needs to be a valid integer above 0!" );
    }

    /**
     * Load the object this proxy object represent
     *
     * @return Abstracts\DomainObject
     */
    public function load()
    {
        return $this->repository->load( $this->type, $this->id );
    }
}
