<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception;

use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface;
use RuntimeException;
use Throwable;

final class UnsupportedSortClauseException extends RuntimeException
{
    public function __construct(string $name, $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            'Could not find %s for %s sort clause',
            SortClauseParserInterface::class,
            $name
        );

        parent::__construct($message, $code, $previous);
    }
}
