<?php

/**
 * File containing the SessionInput class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * SessionInput view model.
 */
class SessionInput extends ValueObject
{
    /** @var string */
    public $login;

    /** @var string */
    public $password;
}
