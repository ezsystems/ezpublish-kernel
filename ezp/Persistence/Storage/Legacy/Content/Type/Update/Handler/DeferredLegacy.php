<?php
/**
 * File containing the DeferredLegacy Type Update Handler class
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
 * DeferredLegacy based type update handler
 */
class DeferredLegacy extends Handler
{
    /**
     * ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     *
     * @var mixed
     */
    protected $contentTypeGateway;

    /**
     * Creates a new content type update handler
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     */
    public function __construct( Gateway $contentTypeGateway )
    {
        $this->contentTypeGateway = $contentTypeGateway;
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
    }

    /**
     * Deletes $fromType and all of its field definitions
     *
     * @param \ezp\Persistence\Content\Type $fromType
     * @return void
     */
    public function deleteOldType( $fromType )
    {
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
            $fromType->id,
            $toType->status,
            Type::STATUS_MODIFIED
        );

        $script = eZScheduledScript::create( 'syncobjectattributes.php',
            eZINI::instance( 'ezscriptmonitor.ini' )->variable( 'GeneralSettings', 'PhpCliCommand' ) .
            ' extension/ezscriptmonitor/bin/' . eZScheduledScript::SCRIPT_NAME_STRING .
            ' -s ' . eZScheduledScript::SITE_ACCESS_STRING . ' --classid=' . $fromType->id );
        $script->store();
    }
}
