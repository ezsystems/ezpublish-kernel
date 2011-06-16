<?php
/**
 * Storage Engine Handler Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base;
interface Interface_StorageEngine_Handler
{
    /**
     * Setups current instance with reference to storage engine object that created it.
     *
     * @param Interface_StorageEngine $engine
     */
    public function __construct( Interface_StorageEngine $engine );
}
