<?php
/**
 * Storage Engine Handler Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base\Interfaces\StorageEngine;
interface Handler
{
    /**
     * Setups current instance with reference to storage engine object that created it.
     *
     * @param StorageEngine $engine
     */
    public function __construct( StorageEngine $engine );
}
