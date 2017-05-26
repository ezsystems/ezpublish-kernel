<?php

/**
 * File containing the UserProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;

class UserProcessor extends FieldTypeProcessor
{
    public function postProcessValueHash($outgoingValueHash)
    {
        unset($outgoingValueHash['passwordHash']);
        unset($outgoingValueHash['passwordHashType']);

        return $outgoingValueHash;
    }
}
