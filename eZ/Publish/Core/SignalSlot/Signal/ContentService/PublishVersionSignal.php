<?php
/**
 * PublishVersionSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * PublishVersionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class PublishVersionSignal extends Signal
{
    /**
     * VersionInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public $versionInfo;

}

