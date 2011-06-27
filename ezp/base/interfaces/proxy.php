<?php
/**
 * File contains Interface for Proxy object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Interface for Proxy object
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
interface ProxyInterface
{
    /**
     * Load the object this proxy object represent
     *
     * @return \ezp\base\AbstractModel
     */
    public function load();
}
