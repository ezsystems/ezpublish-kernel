<?php

/**
 * File containing the XmlTextProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    public function preProcessFieldSettingsHash($incomingSettingsHash)
    {
        if (isset($incomingSettingsHash['tagPreset'])) {
            switch ($incomingSettingsHash['tagPreset']) {
                case 'TAG_PRESET_DEFAULT':
                    $incomingSettingsHash['tagPreset'] = Type::TAG_PRESET_DEFAULT;
                    break;
                case 'TAG_PRESET_SIMPLE_FORMATTING':
                    $incomingSettingsHash['tagPreset'] = Type::TAG_PRESET_SIMPLE_FORMATTING;
            }
        }

        return $incomingSettingsHash;
    }

    /**
     * {@inheritDoc}
     */
    public function postProcessFieldSettingsHash($outgoingSettingsHash)
    {
        if (isset($outgoingSettingsHash['tagPreset'])) {
            switch ($outgoingSettingsHash['tagPreset']) {
                case Type::TAG_PRESET_DEFAULT:
                    $outgoingSettingsHash['tagPreset'] = 'TAG_PRESET_DEFAULT';
                    break;
                case Type::TAG_PRESET_SIMPLE_FORMATTING:
                    $outgoingSettingsHash['tagPreset'] = 'TAG_PRESET_SIMPLE_FORMATTING';
            }
        }

        return $outgoingSettingsHash;
    }
}
