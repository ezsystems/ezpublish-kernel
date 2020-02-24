<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

final class SortSpecLexer implements SortSpecLexerInterface
{
    private const K_ASC = 'asc';
    private const K_DESC = 'desc';

    private const ID_PATTERN = '[a-zA-Z_][a-zA-Z0-9_]*';
    private const FLOAT_PATTERN = '-?[0-9]+\.[0-9]+';
    private const INT_PATTERN = '-?[0-9]+';

    /** @var string */
    private $input;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token[] */
    private $tokens = [];

    /** @var int|null */
    private $position;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token|null */
    private $current;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token|null */
    private $next;

    public function getAll(): iterable
    {
        return $this->tokens;
    }

    public function consume(): Token
    {
        $this->current = $this->next;
        $this->next = $this->tokens[++$this->position] ?? null;

        return $this->current;
    }

    public function isEOF(): bool
    {
        return $this->next === null || $this->next->isA(Token::TYPE_EOF);
    }

    public function peek(): ?Token
    {
        return $this->next;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function tokenize(string $input): void
    {
        $this->reset();

        $this->input = $input;
        $this->tokens = [];
        foreach ($this->split($input) as $match) {
            [$value, $position] = $match;
            $value = trim($value);

            if ($value === '') {
                // Skip whitespaces
                continue;
            }

            $this->tokens[] = new Token(
                $this->getTokenType($value),
                $value,
                $position
            );
        }

        $this->tokens[] = new Token(Token::TYPE_EOF);
        $this->next = $this->tokens[0] ?? null;
    }

    private function reset(): void
    {
        $this->position = 0;
        $this->next = null;
        $this->current = null;
    }

    private function split(string $input): array
    {
        $regexp = sprintf(
            '/^(asc)|(desc)|(\\.)|(,)|(%s)|\s+$/iu',
            implode(')|(', [
                self::FLOAT_PATTERN,
                self::INT_PATTERN,
                self::ID_PATTERN,
            ]),
        );

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;

        return preg_split($regexp, $input, -1, $flags);
    }

    private function getTokenType(string $value): string
    {
        switch ($value) {
            case self::K_ASC:
                return Token::TYPE_ASC;
            case self::K_DESC:
                return Token::TYPE_DESC;
            case '.':
                return Token::TYPE_DOT;
            case ',':
                return Token::TYPE_COMMA;
        }

        if ($this->isInt($value)) {
            return Token::TYPE_INT;
        }

        if ($this->isFloat($value)) {
            return Token::TYPE_FLOAT;
        }

        if ($this->isID($value)) {
            return Token::TYPE_ID;
        }

        return Token::TYPE_NONE;
    }

    private function isInt(string $value): bool
    {
        return preg_match('/^' . self::INT_PATTERN . '$/', $value) === 1;
    }

    private function isFloat(string $value): bool
    {
        return preg_match('/^' . self::FLOAT_PATTERN . '$/', $value) === 1;
    }

    private function isID(string $value): bool
    {
        return preg_match('/^' . self::ID_PATTERN . '$/', $value) === 1;
    }
}
