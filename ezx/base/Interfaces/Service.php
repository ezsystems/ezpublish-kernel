<?php
/**
 * Repository Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base\Interfaces;
interface Service
{
    /**
     * Setups current instance with reference to repository object that created it.
     *
     * @param Repository $repository
     * @param StorageEngine $se
     */
    public function __construct( Repository $repository, StorageEngine $se );
}
