<?php
/**
 * File containing the ezp\Content\Services\ContentType class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

namespace ezp\Content\Services;

/**
 * Content Service, extends repository with content specific operations
 *
 * @package ezp
 * @subpackage content
 */
use \ezp\Base\Exception;
class ContentType extends \ezp\Base\AbstractService
{
    /**
     * Get an Content Type object by id
     *
     * @param int $contentTypeId
     * @return \ezp\Content\Type\Type
     * @throws Exception\NotFound
     */
    public function load( $contentTypeId )
    {
        $contentType = $this->handler->contentTypeHandler()->load( $contentTypeId );
        if ( !$contentType )
            throw new Exception\NotFound( 'Content\Type', $contentTypeId );
        return $contentType;
    }

    /**
     * Get an Content Type by identifier
     *
     * @param string $identifier
     * @return \ezp\Content\Type\Type
     * @throws Exception\NotFound
     */
    public function loadByIdentifier( $identifier )
    {
        $contentTypes = $this->handler->contentTypeHandler()->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new Exception\NotFound( 'Content\Type', $identifier );
        return $contentTypes[0];
    }
}
