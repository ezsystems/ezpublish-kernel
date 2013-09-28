<?php
/**
 * File containing the BaseEvent class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\FieldType\FieldStorage\Events;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Abstract class for FieldType storage events.
 * Will be provided to FieldStorage handlers when applicable,
 * and provide access to the event properties (field and versionInfo).
 */
abstract class BaseEvent
{
    /**
     * Field the event occurred on
     * @var Field
     */
    protected $field;

    /**
     * VersionInfo of the Content the affected field belongs to
     * @var VersionInfo
     */
    protected $versionInfo;

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->versionInfo;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field
     */
    public function setField( Field $field )
    {
        $this->field = $field;
    }

    /**
     * @return VersionInfo
     */
    public function setVersionInfo( VersionInfo $versionInfo )
    {
        $this->versionInfo = $versionInfo;
    }
}
