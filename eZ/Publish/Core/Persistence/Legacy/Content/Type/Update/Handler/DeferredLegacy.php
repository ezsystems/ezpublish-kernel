<?php

/**
 * File containing the DeferredLegacy Type Update Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;
use eZ\Publish\SPI\Persistence\Content\Type;

/**
 * DeferredLegacy based type update handler.
 */
class DeferredLegacy extends Handler
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway */
    protected $contentTypeGateway;

    /**
     * Creates a new content type update handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway $contentTypeGateway
     */
    public function __construct(Gateway $contentTypeGateway)
    {
        $this->contentTypeGateway = $contentTypeGateway;
    }

    /**
     * Updates existing content objects from $fromType to $toType.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $fromType
     * @param \eZ\Publish\SPI\Persistence\Content\Type $toType
     */
    public function updateContentObjects(Type $fromType, Type $toType)
    {
    }

    /**
     * Deletes $fromType and all of its field definitions.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $fromType
     */
    public function deleteOldType(Type $fromType)
    {
    }

    /**
     * Publishes $toType to $newStatus.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $toType
     * @param int $newStatus
     */
    public function publishNewType(Type $toType, $newStatus)
    {
        $this->contentTypeGateway->publishTypeAndFields(
            $toType->id,
            $toType->status,
            Type::STATUS_MODIFIED
        );

        $script = \eZScheduledScript::create(
            'syncobjectattributes.php',
            \eZINI::instance('ezscriptmonitor.ini')->variable('GeneralSettings', 'PhpCliCommand') .
            ' extension/ezscriptmonitor/bin/' . \eZScheduledScript::SCRIPT_NAME_STRING .
            ' -s ' . \eZScheduledScript::SITE_ACCESS_STRING . ' --classid=' . $toType->id
        );
        $script->store();
    }
}
