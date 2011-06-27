<?php
/**
 * File contains Content Handler Storage Engine Interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Content Handler Storage Engine Interface
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base\StorageEngine;
interface ContentHandlerInterface extends HandlerInterface
{
    /**
     * Create Content object
     *
     * @param \ezp\content\Content $content
     * @return \ezp\content\Content
     */
    public function create( \ezx\content\Content $content );

    /**
     * Get Content object by id
     *
     * @param int $id
     * @return \ezp\content\Content
     */
    public function load( $id );
}
