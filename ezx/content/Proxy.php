<?php
/**
 * Content model implementation for Proxy object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

namespace ezp\content;
class Proxy implements \ezp\base\ProxyInterface
{
    /**
     * @var \ezx\base\Interfaces\Service
     */
    protected $service;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $loadFunctionName;

    /**
     * Setup proxy object with enough info to be able to perform a load operation on the object it proxies.
     *
     * @param \ezx\base\Interfaces\Service $service
     * @param int $id Primary id
     * @param string $loadFunctionName Optional, defines which function on handler to call, 'load' by default.
     * @throws \InvalidArgumentException If $id is not a int value above zero.
     */
    public function __construct( \ezx\base\Interfaces\Service $service, $id, $loadFunctionName = 'load' )
    {
        $this->service = $service;
        $this->id = (int) $id;
        $this->loadFunctionName = $loadFunctionName;
        if ( $this->id === 0 )
            throw new \InvalidArgumentException( "Id parameter needs to be a valid integer above 0!" );
    }

    /**
     * Load the object this proxy object represent
     *
     * @return Abstracts\ContentModel
     */
    public function load()
    {
        $fn = $this->loadFunctionName;
        return $this->service->$fn( $this->id );
    }
}
