<?php
/**
 * File containing the EventAware class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\FieldType\FieldStorage;

use eZ\Publish\SPI\FieldType;

/**
 * Can be implemented by {@see eZ\Publish\SPI\FieldStorage} handlers to receive storage events.
 *
 *
 */
interface EventAware
{
    /**
     * Handles storage event $event.
     *
     * $event provides the VersionInfo and Field objects the even is occuring on
     *
     * @param Event $event
     * @param array $context
     *
     * @return bool True if data was modified and needs to be stored
     */
    public function handleEvent( Event $event, array $context );
}
