<?php
/**
 * Identifier Repository Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

namespace ezx\doctrine\model;
interface Interface_IdentifierRepository
{
    /**
     * Get an object by identifier
     *
     * @param string $type
     * @param string $identifier
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadByIdentifier( $type, $identifier );
}
