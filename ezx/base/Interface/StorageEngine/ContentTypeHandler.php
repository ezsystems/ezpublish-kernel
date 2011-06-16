<?php
/**
 * ContentType Handler Storage Engine Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base;
interface Interface_StorageEngine_ContentTypeHandler extends Interface_StorageEngine_Handler
{
    /**
     * Create ContentType object
     *
     * @param \ezx\content\ContentType $contentType
     * @return \ezx\content\ContentType
     */
    public function create( \ezx\content\ContentType $contentType );

    /**
     * Get ContentType object by id
     *
     * @param int $id
     * @return \ezx\content\ContentType
     */
    public function load( $id );
}
