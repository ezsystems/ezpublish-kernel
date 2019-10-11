<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Compare;

use eZ\Publish\SPI\FieldType\Comparable;
use OutOfBoundsException;

final class FieldRegistry
{
    /** @var \eZ\Publish\SPI\FieldType\Comparable[] */
    private $types = [];

    /**
     * @param \eZ\Publish\SPI\FieldType\Comparable[] $types
     */
    public function __construct(array $types = [])
    {
        foreach ($types as $name => $type) {
            $this->registerType($name, $type);
        }
    }

    public function registerType(string $name, Comparable $type): void
    {
        $this->types[$name] = $type;
    }

    public function getType(string $name): Comparable
    {
        if (!isset($this->types[$name])) {
            throw new OutOfBoundsException(
                sprintf(
                    'Field type "%s" is not comparable.',
                    $name,
                )
            );
        }

        return $this->types[$name];
    }
}
