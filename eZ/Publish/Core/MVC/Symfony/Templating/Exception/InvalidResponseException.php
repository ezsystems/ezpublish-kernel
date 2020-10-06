<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Exception;

use eZ\Publish\Core\Base\Exceptions\ForbiddenException;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\Base\TranslatableBase;

class InvalidResponseException extends ForbiddenException implements Translatable
{
    use TranslatableBase;

    public function __construct(string $whatIsWrong)
    {
        parent::__construct(
            'Response is invalid: %whatIsWrong%',
            [
                '%whatIsWrong%' => $whatIsWrong,
            ]
        );
    }
}
