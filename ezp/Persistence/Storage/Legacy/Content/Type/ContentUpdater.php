<?php
/**
 * File containing the content updater class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type;
use ezp\Persistence\Storage\Legacy\Content;

/**
 * Class to update content objects to a new type version
 */
class ContentUpdater
{
    /**
     * Content gateway
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contenGateway;

    /**
     * Content type gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected $contentTypeGateway;

    /**
     * Creates a new content updater
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contenGateway
     */
    public function __construct(
        Content\Type\Gateway $contentTypeGateway,
        Content\Gateway $contenGateway )
    {
        $this->contenGateway = $contenGateway;
        $this->contentTypeGateway = $contentTypeGateway;
    }

    /**
     * Determines the neccessary update actions
     *
     * @param mixed $contentTypeId
     * @return ContentUpdater\Action[]
     */
    public function determineActions( $contentTypeId )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Applies all given updates
     *
     * @param mixed $contentTypeId
     * @param ContentUpdater\Action[] $actions
     * @return void
     */
    public function applyUpdates( $contentTypeId, array $actions )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Publishes the content type in new version
     *
     * @param mixed $contentTypeId
     * @return void
     */
    public function publish( $contentTypeId )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
