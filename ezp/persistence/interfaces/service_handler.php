<?php
/**
 * File contains Service Handler Interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\persistence;

/**
 * Service Handler Interface
 *
 * @package ezp
 * @subpackage persistence
 */
interface ServiceHandlerInterface
{
    /**
     * Setups current handler instance with reference to storage engine object that created it.
     *
     * @param \ezp\persistence\RepositoryHandlerInterface $engine
     * @param object $backend Optional, use this argument if storage engine needs to pass backend object to handlers
     *                        to be able to handle operations.
     */
    public function __construct( RepositoryHandlerInterface $engine, $backend = null );
}
