<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating;

use eZ\Publish\SPI\Options\MutableOptionsBag;

final class RenderOptions implements MutableOptionsBag
{
    /** @var array */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function all(): array
    {
        return $this->options;
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->options[$key];
        }

        return $default;
    }

    /**
     * @param mixed|null $value
     */
    public function set(string $key, $value): void
    {
        $this->options[$key] = $value;
    }

    public function has(string $key): bool
    {
        return !empty($this->options[$key]);
    }

    public function remove(string $key): void
    {
        unset($this->options[$key]);
    }
}
