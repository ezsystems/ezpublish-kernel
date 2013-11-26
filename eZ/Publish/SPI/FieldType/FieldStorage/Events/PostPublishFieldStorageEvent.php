<?php
/**
 * File containing the PostPublishEvent class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType\FieldStorage\Events;

use eZ\Publish\SPI\FieldType\FieldStorage\Event as FieldStorageEvent;

/**
 * Event triggered after a field of the FieldType has been published.
 */
class PostPublishFieldStorageEvent extends BaseEvent implements FieldStorageEvent
{
}
