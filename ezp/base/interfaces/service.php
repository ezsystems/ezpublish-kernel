<?php
/**
 * File contains Service Interface, for services attached to repository
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Service Interface
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
interface ServiceInterface
{
    /**
     * Setups service with reference to repository object that created it & corresponding storage engine handler
     *
     * @param Repository $repository
     * @param StorageEngineInterface $handler
     */
    public function __construct( Repository $repository, StorageEngineInterface $se );
}
