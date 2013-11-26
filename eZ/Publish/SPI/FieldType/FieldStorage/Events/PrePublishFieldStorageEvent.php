<?php
/**
 * File containing the PrePublishEvent class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType\FieldStorage\Events;

use eZ\Publish\SPI\FieldType\FieldStorage\Event as FieldStorageEvent;

/**
 * Event triggered before a field of the FieldType is published.
 */
class PrePublishFieldStorageEvent extends BaseEvent implements FieldStorageEvent
{
}
