<?php
/**
 * File containing the Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Media;
use eZ\Publish\Core\Repository\FieldType\BinaryFile\Handler as BinaryFileHandler;

/**
 * Media file handler
 */
class Handler extends BinaryFileHandler
{
    const PLUGINSPAGE_FLASH = 'http://www.adobe.com/go/EN_US-H-GET-FLASH',
          PLUGINSPAGE_QUICKTIME = 'http://quicktime.apple.com',
          PLUGINSPAGE_REAL = 'http://www.real.com',
          PLUGINSPAGE_SILVERLIGHT = 'http://go.microsoft.com/fwlink/?LinkID=108182',
          PLUGINSPAGE_WINDOWSMEDIA = 'http://microsoft.com/windows/mediaplayer/en/download/';

    /**
     * Returns default plugin page depending on $mediaType
     *
     * @param $mediaType
     * @return string
     */
    public function getPluginspageByType( $mediaType )
    {
        switch ( $mediaType )
        {
            case Type::TYPE_FLASH:
                $pluginPage = self::PLUGINSPAGE_FLASH;
                break;

            case Type::TYPE_QUICKTIME:
                $pluginPage = self::PLUGINSPAGE_QUICKTIME;
                break;

            case Type::TYPE_REALPLAYER:
                $pluginPage = self::PLUGINSPAGE_REAL;
                break;

            case Type::TYPE_SILVERLIGHT:
                $pluginPage = self::PLUGINSPAGE_SILVERLIGHT;
                break;

            case Type::TYPE_WINDOWSMEDIA:
                $pluginPage = self::PLUGINSPAGE_WINDOWSMEDIA;
                break;

            case Type::TYPE_HTML5_VIDEO:
            case Type::TYPE_HTML5_AUDIO:
            default:
                $pluginPage = '';
        }

        return $pluginPage;
    }
}
