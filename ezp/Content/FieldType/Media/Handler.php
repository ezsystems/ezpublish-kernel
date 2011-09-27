<?php
/**
 * File containing the Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Media;
use ezp\Content\FieldType\Value as ValueInterface,
    ezp\Content\FieldType\BinaryFile\Handler as BinaryFileHandler,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Io\BinaryFile,
    ezp\Io\ContentType,
    ezp\Io\SysInfo,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository;

/**
 * Media file handler
 */
class Handler extends BinaryFileHandler
{
    /**
     * Returns default plugin page depending on $mediaType
     *
     * @param $mediaType
     * @return string
     */
    public function getPluginspageByType( $mediaType )
    {
        $pluginPage = '';
        switch ( $mediaType )
        {
            case Value::TYPE_FLASH:
                $pluginPage = 'http://www.adobe.com/go/EN_US-H-GET-FLASH';
                break;

            case Value::TYPE_QUICKTIME:
                $pluginPage = 'http://quicktime.apple.com';
                break;

            case Value::TYPE_REALPLAYER:
                $pluginPage = 'http://www.real.com/';
                break;

            case Value::TYPE_SILVERLIGHT:
                $pluginPage = 'http://go.microsoft.com/fwlink/?LinkID=108182';
                break;

            case Value::TYPE_WINDOWSMEDIA:
                $pluginPage = 'http://microsoft.com/windows/mediaplayer/en/download/';
                break;

            case Value::TYPE_HTML5_VIDEO:
            case Value::TYPE_HTML5_AUDIO:
            default:
                $pluginPage = '';
        }

        return $pluginPage;
    }
}
