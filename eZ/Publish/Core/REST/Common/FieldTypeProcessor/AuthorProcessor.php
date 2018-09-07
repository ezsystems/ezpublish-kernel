<?php

/**
 * File containing the AuthorProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;
use eZ\Publish\Core\FieldType\Author\Type;

class AuthorProcessor extends FieldTypeProcessor
{
    /**
     * {@inheritdoc}
     */
    public function preProcessFieldSettingsHash($incomingSettingsHash)
    {
        if (isset($incomingSettingsHash['defaultAuthor'])) {
            switch ($incomingSettingsHash['defaultAuthor']) {
                case 'DEFAULT_CURRENT_USER':
                    $incomingSettingsHash['defaultAuthor'] = Type::DEFAULT_CURRENT_USER;
                    break;
                default:
                    $incomingSettingsHash['defaultAuthor'] = Type::DEFAULT_VALUE_EMPTY;
            }
        }

        return $incomingSettingsHash;
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessFieldSettingsHash($outgoingSettingsHash)
    {
        if (isset($outgoingSettingsHash['defaultAuthor'])) {
            switch ($outgoingSettingsHash['defaultAuthor']) {
                case Type::DEFAULT_CURRENT_USER:
                    $outgoingSettingsHash['defaultAuthor'] = 'DEFAULT_CURRENT_USER';
                    break;
                default:
                    $outgoingSettingsHash['defaultAuthor'] = 'DEFAULT_VALUE_EMPTY';
            }
        }

        return $outgoingSettingsHash;
    }
}
