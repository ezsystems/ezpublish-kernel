<?php
/**
 * Repository Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

namespace ezx\doctrine;
interface Interface_Service
{
    /**
     * Setups current instance with reference to repository object that created it.
     *
     * @param Interface_Repository $repository
     */
    public function __construct( Interface_Repository $repository );
}
