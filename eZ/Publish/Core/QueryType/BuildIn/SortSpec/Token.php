<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

final class Token
{
    public const TYPE_NONE = '<none>';
    public const TYPE_ASC = '<asc>';
    public const TYPE_DESC = '<desc>';
    public const TYPE_ID = '<id>';
    public const TYPE_DOT = '<.>';
    public const TYPE_COMMA = '<,>';
    public const TYPE_INT = '<int>';
    public const TYPE_FLOAT = '<float>';
    public const TYPE_EOF = '<eof>';

    /** @var string */
    private $type;

    /** @var string */
    private $value;

    /** @var int */
    private $position;

    public function __construct(string $type, string $value = '', int $position = -1)
    {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }

    public function isA(string $type): bool
    {
        return $this->type === $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getValueAsFloat(): float
    {
        return (float)$this->value;
    }

    public function getValueAsInt(): int
    {
        return (int)$this->value;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function __toString(): string
    {
        if ($this->value !== null) {
            return "{$this->value} ({$this->type})";
        }

        return "{$this->type}";
    }
}
