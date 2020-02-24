<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec\Tests;

use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecLexerInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token;

/**
 * Dummy \eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecLexerInterface implementation.
 */
final class SortSpecLexerStub implements SortSpecLexerInterface
{
    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token[] */
    private $tokens;

    /** @var string|null */
    private $input;

    /** @var int */
    private $position;

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
        $this->position = -1;
    }

    public function consume(): Token
    {
        ++$this->position;

        return $this->tokens[$this->position];
    }

    public function isEOF(): bool
    {
        return $this->position + 1 >= count($this->tokens) - 1;
    }

    public function tokenize(string $input): void
    {
        $this->input = $input;
    }

    public function getInput(): string
    {
        return (string)$this->input;
    }

    public function peek(): ?Token
    {
        return $this->tokens[$this->position + 1] ?? null;
    }
}
