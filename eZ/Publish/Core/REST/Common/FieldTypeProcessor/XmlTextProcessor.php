<?php
/**
 * File containing the XmlTextProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\XmlText\Type;

class XmlTextProcessor extends FieldTypeProcessor
{
    /**
     * {@inheritDoc}
     */
    public function preProcessFieldSettingsHash( $incomingSettingsHash )
    {
        if ( isset( $incomingSettingsHash["tagPreset"] ) )
        {
            switch ( $incomingSettingsHash["tagPreset"] )
            {
                case 'TAG_PRESET_DEFAULT':
                    $incomingSettingsHash["tagPreset"] = Type::TAG_PRESET_DEFAULT;
                    break;
                case 'TAG_PRESET_SIMPLE_FORMATTING':
                    $incomingSettingsHash["tagPreset"] = Type::TAG_PRESET_SIMPLE_FORMATTING;
            }
        }
        return $incomingSettingsHash;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessFieldSettingsHash( $outgoingSettingsHash )
    {
        if ( isset( $outgoingSettingsHash["tagPreset"] ) )
        {
            switch ( $outgoingSettingsHash["tagPreset"] )
            {
                case Type::TAG_PRESET_DEFAULT:
                    $outgoingSettingsHash["tagPreset"] = 'TAG_PRESET_DEFAULT';
                    break;
                case Type::TAG_PRESET_SIMPLE_FORMATTING:
                    $outgoingSettingsHash["tagPreset"] = 'TAG_PRESET_SIMPLE_FORMATTING';
            }
        }

        return $outgoingSettingsHash;
    }
}
