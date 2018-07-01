<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Message class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values;

/**
 * Base class fro translation messages.
 *
 * Use its extensions: Translation\Singular, Translation\Plural.
 */
abstract class Translation extends ValueObject
{
}
