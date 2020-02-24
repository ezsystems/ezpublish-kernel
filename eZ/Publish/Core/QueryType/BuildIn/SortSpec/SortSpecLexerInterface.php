<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

interface SortSpecLexerInterface
{
    /**
     * Returns analyzed input string.
     */
    public function getInput(): string;

    /**
     * Analyze given string.
     */
    public function tokenize(string $input): void;

    /**
     * Consume and return current token.
     */
    public function consume(): Token;

    /**
     * Returns next token (if available) without moving internal token stream pointer.
     */
    public function peek(): ?Token;

    /**
     * Returns true if there is no more tokens available in the stream.
     */
    public function isEOF(): bool;
}
