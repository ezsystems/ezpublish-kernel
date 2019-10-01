<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Lists;

/**
 * This class represents an element of the list to which the user has no access.
 */
abstract class UnauthorizedListItem
{
    /** @var string */
    private $module;

    /** @var string */
    private $function;

    /** @var array */
    private $payload;

    /**
     * @param string $module
     * @param string $function
     * @param array $payload
     */
    public function __construct(string $module, string $function, array $payload)
    {
        $this->module = $module;
        $this->function = $function;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
