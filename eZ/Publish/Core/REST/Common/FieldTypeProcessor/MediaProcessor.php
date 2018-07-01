<?php

/**
 * File containing the MediaProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\FieldType\Media\Type;

class MediaProcessor extends BinaryInputProcessor
{
    /**
     * {@inheritdoc}
     */
    public function preProcessFieldSettingsHash($incomingSettingsHash)
    {
        if (isset($incomingSettingsHash['mediaType'])) {
            switch ($incomingSettingsHash['mediaType']) {
                case 'TYPE_FLASH':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_FLASH;
                    break;
                case 'TYPE_QUICKTIME':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_QUICKTIME;
                    break;
                case 'TYPE_REALPLAYER':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_REALPLAYER;
                    break;
                case 'TYPE_SILVERLIGHT':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_SILVERLIGHT;
                    break;
                case 'TYPE_WINDOWSMEDIA':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_WINDOWSMEDIA;
                    break;
                case 'TYPE_HTML5_VIDEO':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_HTML5_VIDEO;
                    break;
                case 'TYPE_HTML5_AUDIO':
                    $incomingSettingsHash['mediaType'] = Type::TYPE_HTML5_AUDIO;
            }
        }

        return $incomingSettingsHash;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessFieldSettingsHash($outgoingSettingsHash)
    {
        if (isset($outgoingSettingsHash['mediaType'])) {
            switch ($outgoingSettingsHash['mediaType']) {
                case Type::TYPE_FLASH:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_FLASH';
                    break;
                case Type::TYPE_QUICKTIME:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_QUICKTIME';
                    break;
                case Type::TYPE_REALPLAYER:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_REALPLAYER';
                    break;
                case Type::TYPE_SILVERLIGHT:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_SILVERLIGHT';
                    break;
                case Type::TYPE_WINDOWSMEDIA:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_WINDOWSMEDIA';
                    break;
                case Type::TYPE_HTML5_VIDEO:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_HTML5_VIDEO';
                    break;
                case Type::TYPE_HTML5_AUDIO:
                    $outgoingSettingsHash['mediaType'] = 'TYPE_HTML5_AUDIO';
            }
        }

        return $outgoingSettingsHash;
    }
}
