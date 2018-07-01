<?php

/**
 * File containing the TimeProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\Time\Type;

class TimeProcessor extends FieldTypeProcessor
{
    /**
     * {@inheritdoc}
     */
    public function preProcessFieldSettingsHash($incomingSettingsHash)
    {
        if (isset($incomingSettingsHash['defaultType'])) {
            switch ($incomingSettingsHash['defaultType']) {
                case 'DEFAULT_EMPTY':
                    $incomingSettingsHash['defaultType'] = Type::DEFAULT_EMPTY;
                    break;
                case 'DEFAULT_CURRENT_TIME':
                    $incomingSettingsHash['defaultType'] = Type::DEFAULT_CURRENT_TIME;
            }
        }

        return $incomingSettingsHash;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessFieldSettingsHash($outgoingSettingsHash)
    {
        if (isset($outgoingSettingsHash['defaultType'])) {
            switch ($outgoingSettingsHash['defaultType']) {
                case Type::DEFAULT_EMPTY:
                    $outgoingSettingsHash['defaultType'] = 'DEFAULT_EMPTY';
                    break;
                case Type::DEFAULT_CURRENT_TIME:
                    $outgoingSettingsHash['defaultType'] = 'DEFAULT_CURRENT_TIME';
            }
        }

        return $outgoingSettingsHash;
    }
}
