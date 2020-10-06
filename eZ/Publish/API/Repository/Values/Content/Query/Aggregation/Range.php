<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

use DateTimeInterface;
use eZ\Publish\API\Repository\Values\ValueObject;

final class Range extends ValueObject
{
    /**
     * Beginning of the range (included).
     *
     * @var int|float|\DateTimeInterface|null
     */
    private $from;

    /**
     * End of the range (excluded).
     *
     * @var int|float|\DateTimeInterface|null
     */
    private $to;

    public function __construct($from, $to)
    {
        parent::__construct();

        $this->from = $from;
        $this->to = $to;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s;%s)',
            $this->getRangeValueAsString($this->from),
            $this->getRangeValueAsString($this->to)
        );
    }

    private function getRangeValueAsString($value): string
    {
        if ($value === null) {
            return '*';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ISO8601);
        }

        return (string)$value;
    }

    public static function ofInt(?int $from, ?int $to): self
    {
        return new self($from, $to);
    }

    public static function ofFloat(?float $from, ?float $to): self
    {
        return new self($from, $to);
    }

    public static function ofDateTime(?DateTimeInterface $from, ?DateTimeInterface $to): self
    {
        return new self($from, $to);
    }
}
