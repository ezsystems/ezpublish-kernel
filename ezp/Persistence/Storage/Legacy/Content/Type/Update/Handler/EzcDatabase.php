<?php
/**
 * File containing the EzcDatabase Type Update Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler;
use ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler,
    ezp\Persistence\Storage\Legacy\Content\Type\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater;

/**
 * EzcDatabase based type update handler
 */
class EzcDatabase extends Handler
{
    /**
     * ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     *
     * @var mixed
     */
    protected $contentTypeGateway;

    /**
     * Content updater
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdater;

    /**
     * Creates a new content type update handler
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater $contentUpdater
     */
    public function __construct( Gateway $contentTypeGateway, ContentUpdater $contentUpdater )
    {
        $this->contentTypeGateway = $contentTypeGateway;
        $this->contentUpdater = $contentUpdater;
    }

    /**
     * Updates existing content objects from $fromType to $toType
     *
     * @param \ezp\Persistence\Content\Type $fromType
     * @param \ezp\Persistence\Content\Type $toType
     * @return void
     */
    public function updateContentObjects( $fromType, $toType )
    {
        $actions = $this->contentUpdater->determineActions( $fromType, $toType );
        $this->contentUpdater->applyUpdates( $fromType->id, $actions  );
    }

    /**
     * Deletes $fromType and all of its field definitions
     *
     * @param \ezp\Persistence\Content\Type $fromType
     * @return void
     */
    public function deleteOldType( $fromType )
    {
        $this->contentTypeGateway->deleteType( $fromType->id, $fromType->status );
        $this->contentTypeGateway->deleteGroupAssignementsForType(
            $fromType->id, $fromType->status );
        $this->contentTypeGateway->deleteFieldDefinitionsForType(
            $fromType->id, $fromType->status
        );
    }

    /**
     * Publishes $toType to $newStatus
     *
     * @param \ezp\Persistence\Content\Type $toType
     * @param int $newStatus
     * @return void
     */
    public function publishNewType( $toType, $newStatus )
    {
        $this->contentTypeGateway->publishTypeAndFields(
            $toType->id,
            $toType->status,
            $newStatus
        );
    }
}
