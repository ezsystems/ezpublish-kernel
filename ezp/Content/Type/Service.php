<?php
/**
 * File containing the ezp\Content\Type\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Service as BaseService,
    ezp\Base\Exception\NotFound;

/**
 * Content Service, extends repository with content specific operations
 *
 */
class Service extends BaseService
{
    /**
     * Get an Content Type object by id
     *
     * @param int $contentTypeId
     * @return ezp\Content\Type
     * @throws NotFound
     */
    public function load( $contentTypeId )
    {
        $contentType = $this->handler->contentTypeHandler()->load( $contentTypeId );
        if ( !$contentType )
            throw new NotFound( 'Content\\Type', $contentTypeId );
        return $contentType;
    }

    /**
     * Get an Content Type by identifier
     *
     * @param string $identifier
     * @return ezp\Content\Type
     * @throws NotFound
     */
    public function loadByIdentifier( $identifier )
    {
        $contentTypes = $this->handler->contentTypeHandler()->loadByIdentifier( $identifier );
        if ( !$contentTypes )
            throw new NotFound( 'Content\\Type', $identifier );
        return $contentTypes[0];
    }

    protected function buildDomainObject( ValueObject $vo )
    {

    }
}
