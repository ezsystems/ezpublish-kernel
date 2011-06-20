<?php
/**
 * Storage Engine implementation for doctrine
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\doctrine;
class ContentHandler implements \ezx\base\Interfaces\StorageEngine\ContentHandler
{
    /**
     * Object for storage engine
     *
     * @var \ezx\base\Interfaces\StorageEngine
     */
    protected $se;

    /**
     * Object for doctrine backend
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Setups current instance with storage engine and doctrine object
     *
     * @param \ezx\base\Interfaces\StorageEngine $engine
     * @param object $backend
     */
    public function __construct( \ezx\base\Interfaces\StorageEngine $engine, $backend = null )
    {
        $this->se = $engine;
        $this->em = $backend;
    }

    /**
     * Create Content object
     *
     * @param \ezx\content\Content $content
     * @return \ezx\content\Content
     */
    public function create( \ezx\content\Content $content )
    {
        // @todo Store in backend
        return $content;
    }

    /**
     * Get Content object by id
     *
     * @param int $id
     * @return \ezx\content\Content
     */
    public function load( $id )
    {
        return $this->em->find( "ezx\\content\\Content", (int) $id );
    }
}
