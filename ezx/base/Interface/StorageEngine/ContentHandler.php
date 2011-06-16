<?php
/**
 * Content Handler Storage Engine Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base;
interface Interface_StorageEngine_ContentHandler extends Interface_StorageEngine_Handler
{
    /**
     * Create Content object
     *
     * @param \ezx\content\Content $content 
     * @return \ezx\content\Content
     */
    public function create( \ezx\content\Content $content );

    /**
     * Get Content object by id
     *
     * @param int $id
     * @return \ezx\content\Content
     */
    public function load( $id );
}
