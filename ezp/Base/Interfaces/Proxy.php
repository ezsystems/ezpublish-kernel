<?php
/**
 * File contains Interface for Proxy object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Interfaces;

/**
 * Interface for Proxy object
 *
 * @package ezp
 * @subpackage base
 */
interface Proxy
{
    /**
     * Load the object this proxy object represent
     *
     * @return \ezp\Base\AbstractModel
     */
    public function load();
}
