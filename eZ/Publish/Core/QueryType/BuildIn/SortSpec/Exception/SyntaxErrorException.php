<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec\Exception;

use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token;
use RuntimeException;

final class SyntaxErrorException extends RuntimeException
{
    public static function fromUnexpectedToken(string $input, Token $token, array $expectedTypes): self
    {
        $message = sprintf(
            'Error while parsing sorting specification: "%s": Unexpected token %s (%s) at position %d. Expected one of the following tokens: %s',
            $input,
            $token->getValue(),
            $token->getType(),
            $token->getPosition(),
            implode(' ', $expectedTypes)
        );

        return new self($message);
    }
}
