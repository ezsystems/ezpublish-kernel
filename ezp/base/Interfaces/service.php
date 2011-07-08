<?php
/**
 * File contains Service Interface, for services attached to repository
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base\Interfaces;

/**
 * Repository Services Interface
 *
 * @todo: Change to abstract class instead
 *
 * @package ezp
 * @subpackage base
 */
interface Service
{
    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \ezp\base\Repository $repository
     * @param \ezp\persistence\Interfaces\RepositoryHandler $handler
     */
    public function __construct( \ezp\base\Repository $repository, \ezp\persistence\Interfaces\RepositoryHandler $handler );
}
