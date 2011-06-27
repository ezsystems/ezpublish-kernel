<?php
/**
 * File contains Storage Engine - Handler Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Storage Engine - Handler Interface
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base\StorageEngine;
interface HandlerInterface
{
    /**
     * Setups current handler instance with reference to storage engine object that created it.
     *
     * @param \ezp\base\StorageEngineInterface $engine
     * @param object $backend Optional, use this argument if storage engine needs to pass backend object to handlers
     *                        to be able to handle operations.
     */
    public function __construct( \ezp\base\StorageEngineInterface $engine, $backend = null );
}
